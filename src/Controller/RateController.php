<?php

namespace App\Controller;

use DateTime;
use App\Entity\Rating;
use App\Entity\Series;
use App\Repository\SeriesRepository;
use App\Form\RateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RateController extends AbstractController
{
    /**
     * @Route("/rate/{serieID}", name="series_rate", methods={"GET"})
     */
    public function index_rate(int $serieID, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $serie = $repository->find($serieID);

        $rate = new Rating();
        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        // If form was submited and valid, then add the rating and go back to the last page
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->save_rate($serie, $form, $entityManager);
        }

        // Else render the form
        return $this->render('rate/rate.html.twig', [
            'serie' => $serie,
            'user' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }

    /**
     * Save the given serie's rating to the database.
     */
    private function save_rate(Series $serie, FormInterface $form, EntityManagerInterface $entityManager): Response
    {
        // Creation & initialisation of the rating
        $rate = new Rating();
        $rate->setSeries($serie);
        $rate->setUser($this->getUser());
        $rate->setValue((int)$form->get('value')->getData());
        $rate->setComment($form->get('comment')->getData());
        $rate->setDate(new DateTime());

        $entityManager->persist($rate); // save rating
        $entityManager->flush();    // Update the changes made in the databse

        return $this->redirectToRoute('series_show', ['id' => $serie->getId()]);
    }
}
