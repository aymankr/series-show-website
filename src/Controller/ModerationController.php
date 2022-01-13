<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Search\SearchComments;
use App\Repository\RatingRepository;
use App\Form\ModerationFormType;

class ModerationController extends AbstractController
{
    /**
     * @Route("/moderation", name="moderation")
     */
    public function index(Request $request, RatingRepository $ratingRepository): Response
    {
        $search = new SearchComments();
        $search->page = $request->get('page', 1);

        $form = $this->createForm(ModerationFormType::class, $search);
        $form->handleRequest($request);

        $ratings = $ratingRepository->getRatings($search);

        return $this->render('moderation/index.html.twig', [
            'ratings'=> $ratings,
            'form' => $form->createView()
        ]);
    }
}
