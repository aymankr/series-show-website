<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\SearchSerieFormType;
use App\Search\Search;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Episode;
use App\Entity\Genre;
use App\Entity\Series;
use App\Repository\SeriesRepository;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    // The paths of the pages to render
    private static $accountPage = 'user/account.html.twig';
    private static $mySeriesPage = 'user/user_series.html.twig';

    /**
     * @Route("/account", name="userAccount")
     */
    public function account(): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        return $this->render(UserController::$accountPage);
    }

    /**
     * @Route("/my-series", name="userSeries", methods={"GET"})
     */
    public function userSeries(SeriesRepository $repository, Request $request, EntityManagerInterface $entityManager): Response 
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $search = new Search();
        $search->page = $request->get('page', 1);
        $search->followed = true;
        if (isset($_GET['category'])) {
            $repo = $entityManager->getRepository(Genre::class);
            $c = $repo->createQueryBuilder('g')
                ->where('g.name = :name')
                ->setParameter('name', $_GET['category'])->getQuery()->getResult();

            array_push($search->categories, $c[0]);
        }

        $form = $this->createForm(SearchSerieFormType::class, $search);
        $form->handleRequest($request);

        $serie = $repository->getSeriesUserConnected($search, $this->getUser());

        return $this->render(UserController::$mySeriesPage, [
            'serie' => $serie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/follow-serie/{id}", name="follow")
     */
    public function follow(Series $serie, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $this->getUser()->addSeries($serie);
        $entityManager->persist($this->getUser());
        $entityManager->flush();    // Update the changes made in the databse
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/unfollow-serie/{id}", name="unfollow")
     */
    public function unfollow(Series $serie, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $this->getUser()->removeSeries($serie);
        $entityManager->persist($this->getUser());
        $entityManager->flush();    // Update the changes made in the databse

        if (strpos($request->headers->get('referer'), 'my-series') !== false) {
            return $this->redirectToRoute('userSeries');
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/mark-as-seen/episode{id}", name="markAsSeen")
     */
    public function markAsSeen(Episode $episode, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $this->getUser()->addEpisode($episode);
        $entityManager->persist($this->getUser());
        $entityManager->flush();    // Update the changes made in the databse
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/mark-as-not-seen/episode{id}", name="markAsNotSeen")
     */
    public function markAsNotSeen(Episode $episode, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Verify that the user is connected and is a normal user
        if ($isNotNormalUser = $this->verifyNormalUser()) {
            return $isNotNormalUser;
        }

        $this->getUser()->removeEpisode($episode);
        $entityManager->persist($this->getUser());
        $entityManager->flush();    // Update the changes made in the databse
        return $this->redirect($request->headers->get('referer'));
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