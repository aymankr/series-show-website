<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/comments-moderation", name="comments_moderation")
     */
    public function comments_moderation(): Response
    {
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/add_series", name="add_series")
     */
    public function add_series(): Response
    {
        return $this->redirectToRoute('home');
    }
}
