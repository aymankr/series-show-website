<?php

namespace App\Controller;

use App\Entity\Series;
use App\Entity\Genre;
use App\Form\SearchSerieFormType;
use App\Repository\SeriesRepository;
use App\Search\Search;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/series")
 */
class SeriesController extends AbstractController
{
    /**
     * @Route("/explore", name="explore_series", methods={"GET"})
     */
    public function explore(SeriesRepository $repository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = new Search();
        $search->page = $request->get('page', 1);
        if (isset($_GET['category'])) {
            $repo = $entityManager->getRepository(Genre::class);
            $c = $repo->createQueryBuilder('g')
                ->where('g.name = :name')
                ->setParameter('name', $_GET['category'])->getQuery()->getResult();

            array_push($search->categories, $c[0]);
        }

        $form = $this->createForm(SearchSerieFormType::class, $search);
        $form->handleRequest($request);

        // Get the series to display
        if (!$this->getUser()) {
            $serie = $repository->getSeriesUserNotConnected($search);
        } 
        else {
            $serie = $repository->getSeriesUserConnected($search, $this->getUser());
        }

        return $this->render('series/explore.html.twig', [
            'serie' => $serie,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/presentation/{id}", name="series_presentation", methods={"GET"})
     */
    public function presentation(Series $serie): Response
    {
        return $this->render('series/presentation.html.twig', [
            'serie' => $serie,
        ]);
    }

    /**
     * @Route("/poster/{id}", name="series_poster", methods={"GET"})
     */
    public function poster(Series $serie): Response
    {
        return new Response(
            stream_get_contents($serie->getPoster()),
            200,
            array('Content-Type' => 'image/jpeg')
        );
    }
}
