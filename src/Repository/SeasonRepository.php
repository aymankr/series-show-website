<?php

namespace App\Repository;

use App\Entity\Season;
use App\Entity\Series;
use App\Search\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Season::class);
        $this->paginator = $paginator;
    }

    public function getSeasons(Series $serie, Search $search): PaginationInterface { 
        $query = $this->createQueryBuilder('sea')
            ->select('sea');

        if (!empty($search->searchUser)) {
            $query = $query->join('sea.series', 'ser')
                ->andWhere('ser.id LIKE :id')
                ->setParameter('id', $serie->getId());
        }

        $query = $query->getQuery();

        return $this->paginator->paginate($query, $search->page, 5);
    }
}
