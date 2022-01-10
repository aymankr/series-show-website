<?php

namespace App\Controller;

use App\Repository\SeriesRepository;
use App\Entity\Rating;
use App\Form\RateType;
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
        $serie = $repository->findOneById(($serieID));

        $rate = new Rating();
        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        return $this->render('rate/rate.html.twig', [
            'serie'=> $serie,
            'user' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/rate/{serieID}", name="series_save_rate", methods={"GET"})
     */
    public function save_rate(int $serieID, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $serie = $repository->findOneById(($serieID));

        return $this->render('rate/rate.html.twig', [
            'serie'=> $serie,
            'user' => $this->getUser()
        ]);
    }    

}
