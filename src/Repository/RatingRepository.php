<?php

namespace App\Repository;

use App\Search\SearchComments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Rating;

/**
 * @method Rating|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rating|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rating[]    findAll()
 * @method Rating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Rating::class);
        $this->paginator = $paginator;
    }

    public function getRatings(SearchComments $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('r')
            ->select('r');

        if (!empty($search->searchUser)) {
            $query = $query->join('r.user', 'ru')
                ->andWhere('ru.name LIKE :searchUser')
                ->setParameter('searchUser', "%{$search->searchUser}%");
        }

        if (!empty($search->searchSerie)) {
            $query = $query->join('r.series', 'rs')
                ->andWhere('rs.title LIKE :searchSerie')
                ->setParameter('searchSerie', "%{$search->searchSerie}%");
        }

        $query = $query->getQuery();

        return $this->paginator->paginate($query, $search->page, 5);
    }
}
