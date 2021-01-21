<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\ProjetActivity;
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

    public function findByProjet(Projet $projet)
    {
        return $this
            ->createQueryBuilder('projetActivity')
            ->leftJoin('projetActivity.activity', 'activity')
            ->where('projetActivity.projet = :projet')
            ->andWhere('activity.datetime <= :now')
            ->setParameters([
                'projet' => $projet,
                'now' => new DateTime(),
            ])
            ->orderBy('activity.datetime', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }
}
