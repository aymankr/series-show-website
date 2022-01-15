<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use App\Form\RateFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Rating;
use App\Entity\Series;
use App\Repository\SeriesRepository;
use App\Repository\RatingRepository;
use DateTime;

/**
 * @Route("/user/rate")
 */
class RateController extends AbstractController
{
    // The paths of the pages to render
    private static $newRatePage = 'user/rate.html.twig';
    private static $seeRatePage = 'user/see_rate.html.twig';

    /**
     * @Route("/add/{id}", name="addSeriesRate")
     */
    public function addRate(Series $serie, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $rate = new Rating();
        $form = $this->createForm(RateFormType::class, $rate);
        $form->handleRequest($request);

        // If form was submited and valid, then add the rating and go back to serie's page
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->saveRate($serie, $form, $entityManager);
        }

        // Else render the form
        return $this->render(RateController::$newRatePage, [
            'serie' => $serie,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modify/{id}", name="modifySeriesRate")
     */
    public function modifySeriesRate(Series $serie, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $rating = $serie->getRatingByUser($this->getUser());
        $form = $this->createForm(RateFormType::class, $rating);
        $form->handleRequest($request);

        // If form was submited and valid, then modift the rating and go back to the serie's page
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->saveRateModification($rating, $form, $entityManager);
        }

        // Else render the form
        return $this->render(RateController::$newRatePage, [
            'serie' => $serie,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="deleteSeriesRate")
     */
    public function deleteSeriesRate(RatingRepository $ratingRepository, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $rate = $ratingRepository->find($id);
        $entityManager->remove($rate); 
        $entityManager->flush();       

        return $this->redirect($request->headers->get('referer'));
    }


    /**
     * @Route("/see/{id}", name="seeRate")
     */
    public function seeRate(Series $serie): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }
        
        return $this->render(RateController::$seeRatePage, [
            'serie' => $serie,
            'rating' => $serie->getRatingByUser($this->getUser()),
        ]);
    }

    /**
     * Save the given serie's rating to the database.
     */
    private function saveRate(Series $serie, FormInterface $form, EntityManagerInterface $entityManager): Response
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

        return $this->redirectToRoute('seriesPresentation', ['id' => $serie->getId()]);
    }

    /**
     * Modify the given serie's rating of the current user.
     */
    private function saveRateModification(Rating $rate, FormInterface $form, EntityManagerInterface $entityManager): Response
    {
        // Creation & initialisation of the rating
        $rate->setValue((int)$form->get('value')->getData());
        $rate->setComment($form->get('comment')->getData());
        $rate->setDate(new DateTime());

        $entityManager->persist($rate); // save rating
        $entityManager->flush();        // Update the changes made in the databse

        return $this->redirectToRoute('seriesPresentation', ['id' => $rate->getSeries()->getId()]);
    }

    /**
     * Verify that the user is connected and is not an admin.
     * 
     * @return Response to login page if the user is not connected
     * @return Response to home page if the user connected is an admin
     * @return null if the user connected is not an admin
     */
    private function verifyNormalUser(): ?Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('userLogin');
        }
        if ($this->getUser()->getAdmin()) {
            return $this->redirectToRoute('home');
        }
        return null;
    }
}
