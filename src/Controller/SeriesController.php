<?php

namespace App\Controller;

use App\Entity\Series;
use App\Entity\Genre;
use App\Form\SearchSerieFormType;
use App\Form\SeasonsPresentationFormType;
use App\Repository\CountryRepository;
use App\Repository\GenreRepository;
use App\Repository\SeriesRepository;
use App\Repository\SeasonRepository;
use App\Search\Search;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    public function explore(CountryRepository $countryRepository, GenreRepository $genreRepository, SeriesRepository $repository, Request $request): Response
    {
        $search = new Search();
        $search->page = $request->get('page', 1);
        if (isset($_GET['category'])) {
            $c = $genreRepository->createQueryBuilder('g')
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
    public function presentation(Series $serie, Request $request, PaginatorInterface $paginator): Response
    {
        $seasons = $paginator->paginate($serie->getSeasonsOrdered(), $request->query->getInt('page', 1), 5);

        return $this->render('series/presentation.html.twig', [
            'serie' => $serie,
            'seasons'=> $seasons,
        ]);
    }

     /**
     * @Route("/presentation/{id}/season/{number}", name="series_season_presentation", methods={"GET"})
     */
    public function seasonsPresentation(int $id, int $number, SeriesRepository $seriesRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $serie = $seriesRepository->find($id);
        $season = $serie->getSeasonsOrdered()[$number-1];
        $episodes = $paginator->paginate($season->getEpisodesOrdered(), $request->query->getInt('page', 1), 8);

        return $this->render('series/season.html.twig', [
            'serie' => $serie,
            'seasonNumber' => $number,
            'season' => $season,
            'episodes' => $episodes
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
