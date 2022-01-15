<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\User;

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

    public function getNumberOfSeenEpisodes(User $user, Season $season)
    {
        $query = $this->createQueryBuilder('e')
            ->select('e')
            ->join('e.user', 'u')->join('e.season', 'se')
            ->andWhere("u.id in (:user_id)")
            ->setParameter('user_id', $user->getId())
            ->andWhere("se.id in (:seasonid)")
            ->setParameter('seasonid', $season->getId());

        return $query->getQuery()->getResult();
    }
}
