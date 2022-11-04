<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * @return Evenement[]
     */
    public function findToReminder()
    {
        return $this->createQueryBuilder('evenement')
            ->andWhere('evenement.isReminded = false')
            ->andWhere('evenement.reminderAt <= :now')
            ->orderBy('evenement.reminderAt', 'desc')
            ->setParameter('now', date('Y-m-d H:i') . ':00')
            ->getQuery()
            ->getResult()
            ;
    }
}
