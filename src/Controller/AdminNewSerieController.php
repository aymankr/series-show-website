<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Series;
use App\Entity\Genre;
use App\Entity\Actor;
use App\Entity\ExternalRating;
use App\Entity\Country;
use App\Entity\Season;
use App\Repository\GenreRepository;
use App\Repository\ActorRepository;
use App\Repository\ExternalRatingSourceRepository;
use App\Repository\CountryRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/admin")
 */
class AdminNewSerieController extends AbstractController
{

    private $httpClient;
    private static $omdbKey = "78a49509";

    public function __construct(HttpClientInterface $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @Route("/add-serie/{imdbID}", name="addSerie")
     */
    public function addSerie(string $imdbID, GenreRepository $genreRepository, ActorRepository $actorRepository, 
                              ExternalRatingSourceRepository $externRatingSrcRepo, CountryRepository $countryRepository,
                              EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is an admin
        if ($isNotAdmin = $this->verifyUserIsAdmin()) {
            return $isNotAdmin;
        }

        // Get the imdb infos through the API
        $serieInfos = $this->getOmdbAPIResponseInfos($imdbID);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();   // to access the array data

        // Create the new serie
        $serie = new Series();

        // Add the basic informations
        $serie->setImdb($imdbID);
        $serie->setTitle($propertyAccessor->getValue($serieInfos, '[Title]'));
        $serie->setPlot($propertyAccessor->getValue($serieInfos, '[Plot]'));
        $serie->setAwards($propertyAccessor->getValue($serieInfos, '[Awards]'));
        $serie->setYoutubeTrailer(NULL);
        
        // Add director
        if ($propertyAccessor->getValue($serieInfos, '[Director]') != 'N/A') {
            $serie->setDirector($propertyAccessor->getValue($serieInfos, '[Director]'));
        } else {
            $serie->setDirector(NULL);
        }

        // Add poster
        $posterFile = file_get_contents($propertyAccessor->getValue($serieInfos, '[Poster]'));
        $serie->setPoster($posterFile);
        
        // Add year start & end
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

        return $this->redirectToRoute('seriesPresentation', ['id' => $serie->getId()]);
    }

    /**
     * Requests the omdb API and returns the content of the response.
     */
    private function getOmdbAPIResponseInfos(string $imdbID): array
    {
        $requestUrl = "https://www.omdbapi.com/?i=".$imdbID."&apikey=".AdminController::$omdbKey;
        $apiResponse = $this->httpClient->request('GET', $requestUrl);
        return $apiResponse->toArray();
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

    /**
     * Create and add the external rating of the new serie.
     */
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

    /**
     * Verify that the user is connected and is an admin.
     * 
     * @return Response to login page if the user is not connected
     * @return Response to home page if the user connected is not an admin
     * @return null if the user connected is an admin
     */
    private function verifyUserIsAdmin(): ?Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('userLogin');
        }
        if (!$this->getUser()->getAdmin()) {
            return $this->redirectToRoute('home');
        }
        return null;
    }
}
