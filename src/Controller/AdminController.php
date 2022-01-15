<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CommentsModerationFormType;
use App\Form\AddSerieFormType;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityManagerInterface;
use App\Search\SearchComments;
use App\Repository\GenreRepository;
use App\Repository\ActorRepository;
use App\Repository\SeriesRepository;
use App\Repository\RatingRepository;
use App\Repository\ExternalRatingSourceRepository;
use App\Repository\CountryRepository;
use App\Entity\Series;
use App\Entity\Genre;
use App\Entity\Actor;
use App\Entity\ExternalRating;
use App\Entity\Country;
use App\Entity\Season;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    private $api_client;
    private static $omdb_key = "78a49509";

    public function __construct(HttpClientInterface $client)
    {
        $this->api_client = $client;
    }

    /**
     * @Route("/comments-moderation", name="comments_moderation")
     */
    public function comments_moderation(Request $request, RatingRepository $ratingRepository, SeriesRepository $seriesRepository): Response
    {
        $search = new SearchComments();
        $search->page = $request->get('page', 1);
        if (isset($_GET['id'])) {
            $search->searchSerie = $seriesRepository->find($_GET['id'])->getTitle();
        }

        $form = $this->createForm(CommentsModerationFormType::class, $search);
        $form->handleRequest($request);

        $ratings = $ratingRepository->getRatings($search);

        return $this->render('admin/comments_moderation/see_comments.html.twig', [
            'ratings'=> $ratings,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/search-imdb-serie", name="search_imdb_serie")
     */
    public function search_imdb_serie(Request $request, SeriesRepository $seriesRepository): Response
    {
        // Verify that the user is an admin
        if (!$this->getUser()) {
            return $this->redirectToRoute('user_login');
        }
        if (!$this->getUser()->getAdmin()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(AddSerieFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Get the imdb from the form response
            $userImdb = $form->get('imdb_id')->getData();
            $existingSerie = $seriesRepository->findOneBy(['imdb' => $userImdb]);
            if ($existingSerie) {
                return $this->render('admin/add_series/search_imdb_serie.html.twig', [
                    'form' => $form->createView(),
                    'serieFound' => true,
                    'existingSerie' => $existingSerie
                ]);
            }

            // Request the omdb api
            $request_url = "https://www.omdbapi.com/?i=".$userImdb."&apikey=".AdminController::$omdb_key;
            $api_response = $this->api_client->request('GET', $request_url);

            $serieFound = $api_response->getHeaders()['cf-cache-status'][0] == 'HIT';


            // Present the serie if found
            if ($serieFound) {
                return $this->render('admin/add_series/new_serie_overview.html.twig', [
                    'serie' => $api_response->toArray()
                ]);
            }
            
            // Else ask to give an id again
            return $this->render('admin/add_series/search_imdb_serie.html.twig', [
                'form' => $form->createView(),
                'serieFound' => false,
                'existingSerie' => null
            ]);
            
        }

        return $this->render('admin/add_series/search_imdb_serie.html.twig', [
            'form' => $form->createView(),
            'serieFound' => true,
            'existingSerie' => null
        ]);
    }

    /**
     * @Route("/add-serie/{serieImdb}", name="add_serie")
     */
    public function add_serie(string $serieImdb, GenreRepository $genreRepository, ActorRepository $actorRepository, 
                              ExternalRatingSourceRepository $externRatingSrcRepo, CountryRepository $countryRepository,
                              EntityManagerInterface $entityManager): Response
    {
        // Get the imdb infos through the API
        $requestUrl = "https://www.omdbapi.com/?i=".$serieImdb."&apikey=".AdminController::$omdb_key;
        $apiResponse = $this->api_client->request('GET', $requestUrl);
        $serieInfos = $apiResponse->toArray();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();   // to access the array data

        // Create the new serie
        $serie = new Series();

        // Add the basic informations
        $serie->setImdb($serieImdb);
        $serie->setTitle($propertyAccessor->getValue($serieInfos, '[Title]'));
        $serie->setPlot($propertyAccessor->getValue($serieInfos, '[Plot]'));
        $serie->setDirector($propertyAccessor->getValue($serieInfos, '[Director]'));
        $serie->setAwards($propertyAccessor->getValue($serieInfos, '[Awards]'));
        $serie->setYoutubeTrailer(NULL);
        
        // Add poster
        $posterFile = file_get_contents($propertyAccessor->getValue($serieInfos, '[Poster]'));
        $serie->setPoster($posterFile);
        
        // Year start & end
        $years = explode('-', $propertyAccessor->getValue($serieInfos, '[Year]'));
        $serie->setYearStart(intval($years[0]));
        
        if (count($years) > 1) {
            $serie->setYearEnd(intval($years[1]));
        }
        else {
            $serie->setYearEnd(NULL);
        }
        
        // Add country
        $serieCountry = $this->getNewCountry($propertyAccessor->getValue($serieInfos, '[Country]'), $serie, $countryRepository);
        $serie->addCountry($serieCountry);
        $entityManager->persist($serieCountry);

        // Add ratings if it has
        if (count($propertyAccessor->getValue($serieInfos, '[Ratings]')) > 0) {
            $newExternRating = $this->getNewExternalRating($propertyAccessor->getValue($serieInfos, '[Ratings]')[0]['Value'], 
                                                           $propertyAccessor->getValue($serieInfos, '[imdbVotes]'), 
                                                           $serie, $externRatingSrcRepo);
            $entityManager->persist($newExternRating);                                                           
        }

        // Add genres
        $imdbGenres = explode(', ', $propertyAccessor->getValue($serieInfos, '[Genre]'));
        foreach ($imdbGenres as $imdbGenre) {
            $genre = $this->getNewSerieGenre($imdbGenre, $serie, $genreRepository);
            $serie->addGenre($genre);
            $entityManager->persist($genre);
        }

        // Add actors
        $imdbActors = explode(', ', $propertyAccessor->getValue($serieInfos, '[Actors]'));
        foreach ($imdbActors as $imdbActor) {
            $actor = $this->getNewSerieActor($imdbActor, $serie, $actorRepository);
            $serie->addActor($actor);
            $entityManager->persist($actor);
        }

        // Add seasons
        for ($i = 1; $i <= $propertyAccessor->getValue($serieInfos, '[totalSeasons]'); $i++) {
            $entityManager->persist($this->addNewSeason($i, $serie));
        }

        // Commit the changes to the database
        $entityManager->persist($serie);
        $entityManager->flush();

        return $this->redirectToRoute('series_presentation', ['id' => $serie->getId()]);
    }

    /**
     * Get the corresponding given imdb genre from the database, and create a new genre if necessary.
     */
    private function getNewSerieGenre(string $omdbGenreName, Series $serie, GenreRepository $genreRepository)
    {
        $newSerieGenre = $genreRepository->findOneBy(['name' => $omdbGenreName]);

        // If not found, create a new genre
        if (!$newSerieGenre) {
            $newSerieGenre = new Genre();
            $newSerieGenre->setName($omdbGenreName);
        }
        
        $newSerieGenre->addSeries($serie);  // Add the serie to the genre anyway
        return $newSerieGenre;
    }

    /**
     * Get the corresponding given imdb actor from the database, and create a new actor if necessary.
     */
    private function getNewSerieActor(string $omdbActorName, Series $serie, ActorRepository $actorRepository)
    {
        $newSerieActor = $actorRepository->findOneBy(['name' => $omdbActorName]);

        // If not found, create a new actor
        if (!$newSerieActor) {
            $newSerieActor = new Actor();
            $newSerieActor->setName($omdbActorName);
        }
        
        $newSerieActor->addSeries($serie);  // Add the serie to the actor
        return $newSerieActor;
    }

    /**
     * Get the corresponding given country from the database, and create a new country if necessary.
     */
    private function getNewCountry(string $omdbCountryName, Series $serie, CountryRepository $countryRepository)
    {
        if ($omdbCountryName === 'United States') {
            $omdbCountryName = 'USA';
        }

        $newCountry = $countryRepository->findOneBy(['name' => $omdbCountryName]);

        // If not found, create a new country
        if (!$newCountry) {
            $newCountry = new Country();
            $newCountry->setName($omdbCountryName);
        }
        
        $newCountry->addSeries($serie);  // Add the serie to the country
        return $newCountry;
    }

    private function getNewExternalRating(string $imdbRatingValue, string $imdbVotes, 
                                          Series $serie, ExternalRatingSourceRepository $externRatingSrcRepo)
    {
        $externalRating = new ExternalRating();
        $externalRating->setSource($externRatingSrcRepo->find(ExternalRatingSourceRepository::$IMDB_ID));
        $externalRating->setValue($imdbRatingValue);
        $externalRating->setVotes((int)str_replace( ',', '', $imdbVotes));
        $externalRating->setSeries($serie);
        return $externalRating;
    }

    /**
     * Create a new season with the given number and serie and returns it.
     */
    private function addNewSeason(int $seasonNumber, Series $serie): Season
    {
        $newSeason = new Season();
        $newSeason->setNumber($seasonNumber);
        $newSeason->setSeries($serie);
        $serie->addSeason($newSeason);
        return $newSeason;
    }
}
