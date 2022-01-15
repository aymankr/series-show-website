<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Episode;

/**
 * @method Episode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Episode[]    findAll()
 * @method Episode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }
}
