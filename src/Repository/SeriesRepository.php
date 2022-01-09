<?php

namespace App\Repository;

use App\Entity\Series;
use App\Search\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
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

    public function getSeries(Search $search): PaginationInterface {
        $query = $this->createQueryBuilder('s')
                ->select('s', 'g')
                ->join('s.genre', 'g');

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
            ->andWhere('u.id IS NOT NULL');
        }

        $query = $query->getQuery();

        return $this->paginator->paginate($query, $search->page, 6);
    }

    /**
     * Find a serie by the given id.
     * @return null if there is no serie with the given id in the database.
     * @return Series if the serie corresponding to the id has been found.
     */
    public function findOneById($id): ?Series
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id = :idParameter')
            ->setParameter('idParameter', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    // /**
    //  * @return Series[] Returns an array of Series objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Series
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
