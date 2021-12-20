<?php

namespace App\Controller;

use App\Entity\Series;
use App\Form\SeriesType;
use App\Form\SearchType;
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
     * @Route("/", name="series_index", methods={"GET"})
     */
    public function index(SeriesRepository $repository, Request $request): Response
    {
        $search = new Search();
        $search->page = $request->get('page', 1);
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);
        $series = $repository->getSeries($search);

        return $this->render('series/index.html.twig', [
            'series' => $series,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new", name="series_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $series = new Series();
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($series);
            $entityManager->flush();

            return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('series/new.html.twig', [
            'series' => $series,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/show/{id}", name="series_show", methods={"GET"})
     */
    public function show(Series $series): Response
    {
        return $this->render('series/show.html.twig', [
            'series' => $series
        ]);
    }

    /**
     * @Route("/poster/{id}", name="series_poster", methods={"GET"})
     */
    public function poster(Series $series): Response
    {
        return new Response(
            stream_get_contents($series->getPoster()),
            200,
            array('Content-Type' => 'image/jpeg')
        );
    }
}
