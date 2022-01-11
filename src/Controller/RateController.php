<?php

namespace App\Controller;

use App\Repository\SeriesRepository;
use App\Entity\Rating;
use App\Form\RateType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface as FormFormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RateController extends AbstractController
{
    /**
     * @Route("/rate/{serieID}", name="series_rate", methods={"GET"})
     */
    public function index_rate(int $serieID, Request $request, SeriesRepository $repository): Response
    {
        $serie = $repository->find($serieID);

        $rate = new Rating();
        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        return $this->render('rate/rate.html.twig', [
            'serie' => $serie,
            'user' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/rate_serie/{serieID}", name="series_save_rate", methods={"GET"})
     */
    public function save_rate(int $serieID, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $serie = $repository->find($serieID);
        $rate = new Rating();

        $rate->setDate(new DateTime());
        $rate->setSeries($serie);

        $rate->setUser($this->getUser());
        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        $rate->setComment($form->get('comment')->getData());
        $rate->setValue((int)$form->get('value')->getData());
        $entityManager->persist($rate); // save rating
        $entityManager->flush();    // Update the changes made in the databse

        return $this->redirect($request->headers->get('referer'));
    }
}
