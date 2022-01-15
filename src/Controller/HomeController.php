<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Genre;
use App\Repository\SeriesRepository;

class HomeController extends AbstractController
{
     // The paths of the pages to render
     private static $indexPage = 'home/index.html.twig';
     private static $aboutPage = 'home/about.html.twig';

    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $entityManager, SeriesRepository $seriesRepository): Response
    {
        $categories = $entityManager
        ->getRepository(Genre::class)
        ->findAll();

        $trendingSeries = $seriesRepository->getTrendingSeries();

        return $this->render(HomeController::$indexPage, [
            'categories' => $categories,
            'trendingSeries' => $trendingSeries
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(): Response
    {
        return $this->render(HomeController::$aboutPage);
    }
}
