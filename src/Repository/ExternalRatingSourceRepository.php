<?php

namespace App\Repository;

use App\Entity\ExternalRatingSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExternalRatingSource|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExternalRatingSource|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExternalRatingSource[]    findAll()
 * @method ExternalRatingSource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExternalRatingSourceRepository extends ServiceEntityRepository
{
    public static $IMDB_ID = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalRatingSource::class);
    }
}
