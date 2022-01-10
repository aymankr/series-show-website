<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Form\SearchType;
use App\Repository\SeriesRepository;
use App\Repository\EpisodeRepository;
use App\Search\Search;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/series/user")
 */
class UserSeriesController extends AbstractController
{
    /**
     * @Route("/my-series", name="user_series", methods={"GET"})
     */
    public function user_series(SeriesRepository $repository, Request $request, EntityManagerInterface $entityManager): Response 
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
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

        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $series = $repository->getSeries($search);

        return $this->render('series/user/user_series.html.twig', [
            'series' => $series,
            'form' => $form->createView(),
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/follow_serie/{serieID}", name="follow")
     */
    public function follow(int $serieID, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }

        $this->getUser()->addSeries($repository->find($serieID));
        $entityManager->flush();    // Update the changes made in the databse
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/unfollow_serie/{serieID}", name="unfollow")
     */
    public function unfollow(int $serieID, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }

        $this->getUser()->removeSeries($repository->find($serieID));
        $entityManager->flush();    // Update the changes made in the databse

        if (strpos($request->headers->get('referer'), 'my-series') !== false) {
            return $this->redirectToRoute('user_series');
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/mark_as_seen/episode{episodeID}", name="mark_as_seen")
     */
    public function mark_as_seen(int $episodeID, Request $request, EpisodeRepository $repository, 
                                 EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }


        $this->getUser()->addEpisode($repository->find($episodeID));
        $entityManager->flush();    // Update the changes made in the databse
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/mark_as_not_seen/episode{episodeID}", name="mark_as_not_seen")
     */
    public function mark_as_not_seen(int $episodeID, Request $request, EpisodeRepository $repository, 
                                     EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }

        $this->getUser()->removeEpisode($repository->find($episodeID));
        $entityManager->flush();    // Update the changes made in the databse
        return $this->redirect($request->headers->get('referer'));
    }
}
