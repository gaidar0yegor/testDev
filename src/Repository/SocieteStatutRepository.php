<?php

namespace App\Repository;

use App\Entity\SocieteStatut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SocieteStatut|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocieteStatut|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocieteStatut[]    findAll()
 * @method SocieteStatut[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocieteStatutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocieteStatut::class);
    }
}
