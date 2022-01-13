<?php

namespace App\Repository;

use App\Entity\Series;
use App\Entity\User;
use App\Search\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Series|null find($id, $lockMode = null, $lockVersion = null)
 * @method Series|null findOneBy(array $criteria, array $orderBy = null)
 * @method Series[]    findAll()
 * @method Series[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeriesRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Series::class);
        $this->paginator = $paginator;
    }

    public function getSeriesUserNotConnected(Search $search): PaginationInterface {
        $query = $this->createQueryBuilder('s')
                ->select('s', 'g', 'e')
                ->join('s.genre', 'g')->join('s.externalRating', 'e');

        if (!empty($search->s)) {
            $query = $query->andWhere('s.title LIKE :search')
            ->setParameter('search', "%{$search->s}%");
        }

        if (!empty($search->countries)) {
            $query = $query->join('s.country', 'co')
            ->andWhere('co.id IN (:countries)')
            ->setParameter('countries', $search->countries);
        }

        if (!empty($search->categories)) {
            $query = $query->join('s.genre', 'ca')
            ->andWhere('ca.id IN (:categories)')
            ->setParameter('categories', $search->categories);
        }

        $query = $query->getQuery();

        return $this->paginator->paginate($query, $search->page, 6);
    }

    public function getSeriesUserConnected(Search $search, User $user): PaginationInterface {
        $query = $this->createQueryBuilder('s')
                ->select('s', 'g', 'e')
                ->join('s.genre', 'g')->join('s.externalRating', 'e');

        if (!empty($search->s)) {
            $query = $query->andWhere('s.title LIKE :search')
            ->setParameter('search', "%{$search->s}%");
        }

        if (!empty($search->countries)) {
            $query = $query->join('s.country', 'co')
            ->andWhere('co.id IN (:countries)')
            ->setParameter('countries', $search->countries);
        }

        if (!empty($search->categories)) {
            $query = $query->join('s.genre', 'ca')
            ->andWhere('ca.id IN (:categories)')
            ->setParameter('categories', $search->categories);
        }    
        
        if (!empty($search->followed)) {
            $query = $query->join('s.user', 'u')
            ->andWhere("u.id = (:user_id)")
            ->setParameter('user_id', $user->getId());
        }

        $query = $query->getQuery();

        return $this->paginator->paginate($query, $search->page, 6);
    }
}
