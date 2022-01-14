<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $entityManager, SeriesRepository $seriesRepository): Response
    {
        $categories = $entityManager
        ->getRepository(Genre::class)
        ->findAll();

        $trendingSeries = $seriesRepository->getTrendingSeries();

        return $this->render('home/index.html.twig', [
            'categories' => $categories,
            'trendingSeries' => $trendingSeries
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }
}
