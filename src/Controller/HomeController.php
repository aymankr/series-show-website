<?php

namespace App\Controller;

use App\Entity\Genre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager
        ->getRepository(Genre::class)
        ->findAll();

        return $this->render('home/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}
