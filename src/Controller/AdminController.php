<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CommentsModerationFormType;
use App\Form\AddSerieFormType;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GenreRepository;
use App\Repository\ActorRepository;
use App\Repository\SeriesRepository;
use App\Repository\RatingRepository;
use App\Search\SearchComments;

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
    public function search_imdb_serie(Request $request): Response
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

            // Request the omdb api
            $request_url = "https://www.omdbapi.com/?i=".$form->get('imdb_id')->getData()."&apikey=".AdminController::$omdb_key;
            $api_response = $this->api_client->request('GET', $request_url);

            $serie_found = $api_response->getHeaders()['cf-cache-status'][0] == 'HIT';

            // Present the serie if found
            if ($serie_found) {
                return $this->render('admin/add_series/serie_presentation.html.twig', [
                    'serie' => $api_response->toArray()
                ]);
            }
            
            // Else ask to give an id again
            return $this->render('admin/add_series/search_imdb_serie.html.twig', [
                'form' => $form->createView(),
                'serie_found' => false
            ]);
            
        }

        return $this->render('admin/add_series/search_imdb_serie.html.twig', [
            'form' => $form->createView(),
            'serie_found' => true
        ]);
    }

    /**
     * @Route("/add-serie/{serie-infos}", name="add_serie")
     */
    public function add_serie(array $serie_infos, GenreRepository $genre_repository, ActorRepository $actor_repository, 
                              EntityManagerInterface $entityManager): Response
    {
        /*$serie = new Series();

        // Add the simple informations
        $serie->setImdb($serie_infos['imdbID']);
        $serie->setTitle($serie_infos['Title']);
        $serie->setPlot($serie_infos['Plot']);
        $serie->setDirector($serie_infos['Director']);
        $serie->setPoster($serie_infos['Poster']);
        $serie->setYearStart(explode('-', $serie_infos['Year'])[0]);
        $serie->addCountry($serie_infos['Country']);
        $serie->setAwards($serie_infos['Awards']);
        $serie->setYoutubeTrailer(NULL);
        
        // Year end
        if (count(explode('-', $serie_infos['Year'])) > 1) {
            $serie->setYearEnd(explode('-', $serie_infos['Year'])[1]);
        }
        else {
            $serie->setYearEnd(NULL);
        }

        // Add genres
        foreach ($serie_infos['Genres'] as $genre) {
            $serie->addGenre($genre_repository->findOneBy(['name' => $genre]));
        }

        // Add actors
        foreach ($serie_infos['Actors'] as $actor) {
            $serie->addActor($actor_repository->findOneBy(['name' => $actor]));
        }*/

        return $this->redirectToRoute('search_imdb_serie');
    }
}
