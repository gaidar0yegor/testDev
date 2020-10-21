<?php

namespace App\Repository;

use App\Entity\FaitMarquant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FaitMarquant|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaitMarquant|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaitMarquant[]    findAll()
 * @method FaitMarquant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaitMarquantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaitMarquant::class);
    }
}
