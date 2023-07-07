<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetActivity[]    findAll()
 * @method ProjetActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetActivity::class);
    }

    public function findByProjet(Projet $projet, ?int $limit = null, ?\DateTime $infDate = null, \DateTime $supDate = null)
    {
        $supDate = $supDate ?? new DateTime();

        $qb = $this
            ->createQueryBuilder('projetActivity')
            ->leftJoin('projetActivity.activity', 'activity')
            ->where('projetActivity.projet = :projet')
            ->andWhere('activity.datetime <= :supDate')
            ->setParameters([
                'projet' => $projet,
                'supDate' => $supDate,
            ]);

        if (null !== $infDate) {
            $qb->andWhere('activity.datetime >= :infDate')
                ->setParameter('infDate', $infDate);
        }

        $qb->orderBy('activity.datetime', 'desc');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findBySocieteUser(SocieteUser $societeUser, int $limit = 5)
    {
        return $this->createQueryBuilder('projetActivity')
            ->leftJoin('projetActivity.activity', 'activity')
            ->leftJoin('projetActivity.projet', 'projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->andWhere('projetParticipant.societeUser = :societeUser')
            ->setParameters([
                'societeUser' => $societeUser,
            ])
            ->orderBy('activity.datetime', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
