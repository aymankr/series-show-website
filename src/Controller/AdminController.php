<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use App\Form\CommentsModerationFormType;
use App\Form\AddSerieFormType;
use App\Search\SearchComments;
use App\Repository\SeriesRepository;
use App\Repository\RatingRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    private $httpClient;
    private static $omdbKey = "78a49509";

    // The paths of the pages to render
    private static $searchNewSeriePage = 'admin/add_series/search_imdb_serie.html.twig';
    private static $newSerieOverviewPage = 'admin/add_series/new_serie_overview.html.twig';
    private static $commentsModerationPage = 'admin/comments_moderation/see_comments.html.twig';

    public function __construct(HttpClientInterface $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @Route("/comments-moderation", name="commentsModeration")
     */
    public function commentsModeration(Request $request, RatingRepository $ratingRepository, SeriesRepository $seriesRepository): Response
    {
        // Verify that the user is connected and is an admin
        if ($isNotAdmin = $this->verifyUserIsAdmin()) {
            return $isNotAdmin;
        }

        $search = new SearchComments();
        $search->page = $request->get('page', 1);
        if (isset($_GET['id'])) {
            $search->searchSerie = $seriesRepository->find($_GET['id'])->getTitle();
        }

        $form = $this->createForm(CommentsModerationFormType::class, $search);
        $form->handleRequest($request);

        $ratings = $ratingRepository->getRatings($search);

        return $this->render(AdminController::$commentsModerationPage, [
            'ratings'=> $ratings,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/search-imdb-serie", name="searchImdbSerie")
     */
    public function searchImdbSerie(Request $request, SeriesRepository $seriesRepository): Response
    {
        // Verify that the user is connected and is an admin
        if ($isNotAdmin = $this->verifyUserIsAdmin()) {
            return $isNotAdmin;
        }

        $form = $this->createForm(AddSerieFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userImdb = $form->get('imdbId')->getData();

            // Verify that the given imdb id does not correspond in any serie in the db
            if ($alreadyExists = $this->verifySerieIsNotInDB($userImdb, $form, $seriesRepository)) {
                return $alreadyExists;
            }

            // If not request the omdb api
            $requestUrl = "https://www.omdbapi.com/?i=".$userImdb."&apikey=".AdminController::$omdbKey;
            $apiResponse = $this->httpClient->request('GET', $requestUrl);

            // Present the serie if found
            if ($apiResponse->getHeaders()['cf-cache-status'][0] == 'HIT') {
                return $this->render(AdminController::$newSerieOverviewPage, [
                    'serie' => $apiResponse->toArray()
                ]);
            }
            
            // Else ask to give an id again
            return $this->render(AdminController::$searchNewSeriePage, [
                'form' => $form->createView(),
                'serieFound' => false,
                'existingSerie' => null
            ]);
            
        }

        return $this->render(AdminController::$searchNewSeriePage, [
            'form' => $form->createView(),
            'serieFound' => true,
            'existingSerie' => null
        ]);
    }

    /**
     * Verify that the imdb given by the user does not correspond to any serie in the database.
     * 
     * @param string $userImdb the imdb id given by the user through the form
     * @param FormInterface $submitedForm the form submited by the user
     * 
     * @return Response to search new serie page if found in db
     * @return null if not found in db
     */
    private function verifySerieIsNotInDB(string $userImdb, FormInterface $submitedForm, SeriesRepository $seriesRepository): ?Response
    {
        $existingSerie = $seriesRepository->findOneBy(['imdb' => $userImdb]);

        // If found, tell the admin that the serie already exists in the database
        if ($existingSerie) {
            return $this->render(AdminController::$searchNewSeriePage, [
                'form' => $submitedForm->createView(),
                'serieFound' => true,
                'existingSerie' => $existingSerie
            ]);
        }

        // Else return null
        return null;
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
