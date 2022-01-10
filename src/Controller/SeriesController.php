<?php

namespace App\Controller;

use App\Entity\Series;
use App\Entity\Genre;
use App\Form\SeriesType;
use App\Form\SearchType;
use App\Repository\SeriesRepository;
use App\Search\Search;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function PHPUnit\Framework\stringContains;

/**
 * @Route("/series")
 */
class SeriesController extends AbstractController
{
    /**
     * @Route("/", name="series_index", methods={"GET"})
     */
    public function index(SeriesRepository $repository, Request $request, EntityManagerInterface $entityManager): Response
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

        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $series = $repository->getSeries($search);
        return $this->render('series/index.html.twig', [
            'series' => $series,
            'form' => $form->createView(),
            'user' => $this->getUser()
        ]);
    }

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

        return $this->render('series/user_series.html.twig', [
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

        $this->getUser()->addSeries($repository->findOneById($serieID));
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

        $this->getUser()->removeSeries($repository->findOneById($serieID));
        $entityManager->flush();    // Update the changes made in the databse

        if (strpos($request->headers->get('referer'), 'my-series') !== false) {
            return $this->redirectToRoute('user_series');
        }
        return $this->redirect($request->headers->get('referer'));
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
            'series' => $series,
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/rate/{serieID}", name="series_rate", methods={"GET"})
     */
    public function index_rate(int $serieID, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $serie = $repository->findOneById(($serieID));
        return $this->render('series/rate.html.twig', [
            'serie'=> $serie,
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/rate/{serieID}", name="series_save_rate", methods={"GET"})
     */
    public function save_rate(int $serieID, Request $request, SeriesRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $serie = $repository->findOneById(($serieID));
        return $this->render('series/rate.html.twig', [
            'serie'=> $serie,
            'user' => $this->getUser()
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
