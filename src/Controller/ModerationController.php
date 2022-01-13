<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Search\SearchComments;
use App\Repository\RatingRepository;
use App\Form\ModerationFormType;
use App\Repository\SeriesRepository;

class ModerationController extends AbstractController
{
    /**
     * @Route("/moderation", name="moderation")
     */
    public function index(Request $request, RatingRepository $ratingRepository, SeriesRepository $seriesRepository): Response
    {
        $search = new SearchComments();
        $search->page = $request->get('page', 1);
        if (isset($_GET['id'])) {
            $search->searchSerie = $seriesRepository->find($_GET['id'])->getTitle();
        }

        $form = $this->createForm(ModerationFormType::class, $search);
        $form->handleRequest($request);

        $ratings = $ratingRepository->getRatings($search);

        return $this->render('moderation/index.html.twig', [
            'ratings'=> $ratings,
            'form' => $form->createView()
        ]);
    }
}
