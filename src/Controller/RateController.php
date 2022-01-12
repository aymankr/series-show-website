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

/**
 * @Route("/rate")
 */
class RateController extends AbstractController
{
    /**
     * @Route("/add/{id}", name="add_series_rate", methods={"GET"})
     */
    public function add_rate(Series $serie, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }
        $rate = new Rating();
        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        // If form was submited and valid, then add the rating and go back to serie's page
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->save_rate($serie, $form, $entityManager);
        }

        // Else render the form
        return $this->render('rate/rate.html.twig', [
            'serie' => $serie,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modify/{id}", name="modify_series_rate", methods={"GET"})
     */
    public function modify_rate(Series $serie, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        // Verify that a user is loged in
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }

        $rating = $serie->getRatingByUser($this->getUser());
        $form = $this->createForm(RateType::class, $rating);
        $form->handleRequest($request);

        // If form was submited and valid, then modift the rating and go back to the serie's page
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->save_rate_modification($rating, $form, $entityManager);
        }

        // Else render the form
        return $this->render('rate/rate.html.twig', [
            'serie' => $serie,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/see/{id}", name="see_rate")
     */
    public function see_rate(Series $serie): Response
    {
        // Verify that a user is loged in
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }
        
        return $this->render('rate/see_rate.html.twig', [
            'serie' => $serie,
            'rating' => $serie->getRatingByUser($this->getUser()),
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

    /**
     * Modify the given serie's rating of the current user.
     */
    private function save_rate_modification(Rating $rate, FormInterface $form, EntityManagerInterface $entityManager): Response
    {
        // Creation & initialisation of the rating
        $rate->setValue((int)$form->get('value')->getData());
        $rate->setComment($form->get('comment')->getData());
        $rate->setDate(new DateTime());

        $entityManager->persist($rate); // save rating
        $entityManager->flush();        // Update the changes made in the databse

        return $this->redirectToRoute('series_show', ['id' => $rate->getSeries()->getId()]);
    }
}
