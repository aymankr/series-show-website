<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AddSerieType;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    public function comments_moderation(): Response
    {
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/add_series", name="add_series")
     */
    public function add_series(Request $request): Response
    {
        // Verify that the user is an admin
        if (!$this->getUser()) {
            return $this->redirectToRoute('user_login');
        }
        if (!$this->getUser()->getAdmin()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(AddSerieType::class);
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
            return $this->render('admin/add_series/index.html.twig', [
                'form' => $form->createView(),
                'serie_found' => false
            ]);
            
        }

        return $this->render('admin/add_series/index.html.twig', [
            'form' => $form->createView(),
            'serie_found' => true
        ]);
    }
}
