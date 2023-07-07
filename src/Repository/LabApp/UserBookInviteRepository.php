<?php

namespace App\Repository\LabApp;

use App\Entity\LabApp\UserBookInvite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserBookInvite|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBookInvite|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBookInvite|null findOneByInvitationToken(string $token)
 * @method UserBookInvite[]    findAll()
 * @method UserBookInvite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBookInviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBookInvite::class);
    }

    // /**
    //  * @return UserBookInvite[] Returns an array of UserBookInvite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserBookInvite
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
