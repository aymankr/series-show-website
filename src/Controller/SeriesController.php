<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SearchSerieFormType;
use App\Search\Search;
use App\Entity\Series;
use App\Repository\EpisodeRepository;
use App\Repository\GenreRepository;
use App\Repository\SeriesRepository;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/series")
 */
class SeriesController extends AbstractController
{
    // The paths of the pages to render
    private static $explorePage = 'series/explore.html.twig';
    private static $seriePresentationPage = 'series/presentation.html.twig';
    private static $seasonPage = 'series/season.html.twig';

    /**
     * @Route("/explore", name="exploreSeries", methods={"GET"})
     */
    public function explore(GenreRepository $genreRepository, SeriesRepository $repository, Request $request): Response
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
        } else {
            $serie = $repository->getSeriesUserConnected($search, $this->getUser());
        }

        return $this->render(SeriesController::$explorePage, [
            'serie' => $serie,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/presentation/{id}", name="seriesPresentation")
     */
    public function presentation(Series $serie, Request $request, PaginatorInterface $paginator): Response
    {
        $seasons = $paginator->paginate($serie->getSeasonsOrdered(), $request->query->getInt('page', 1), 5);

        return $this->render(SeriesController::$seriePresentationPage, [
            'serie' => $serie,
            'seasons'=> $seasons,
        ]);
    }

     /**
     * @Route("/presentation/{id}/season/{number}", name="seriesSeasonSresentation")
     */
    public function seasonsPresentation(int $id, int $number, SeriesRepository $seriesRepository, EpisodeRepository $episodeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $serie = $seriesRepository->find($id);
        $season = $serie->getSeasonsOrdered()[$number-1];
        $episodes = $paginator->paginate($season->getEpisodesOrdered(), $request->query->getInt('page', 1), 8);
        $count = $episodeRepository->getNumberOfSeenEpisodes($this->getUser(), $season);

        return $this->render(SeriesController::$seasonPage, [
            'serie' => $serie,
            'seasonNumber' => $number,
            'season' => $season,
            'episodes' => $episodes,
            'numberSeen' => $count
        ]);
    }   

    /**
     * @Route("/poster/{id}", name="seriesPoster")
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
