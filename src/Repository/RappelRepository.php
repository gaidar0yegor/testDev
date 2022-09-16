<?php

namespace App\Repository;

use App\Entity\Rappel;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rappel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rappel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rappel[]    findAll()
 * @method Rappel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RappelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rappel::class);
    }

    /**
     * @return Rappel[]
     */
    public function findToReminder()
    {
        return $this->createQueryBuilder('rappel')
            ->andWhere('rappel.isReminded = false')
            ->andWhere('rappel.reminderAt <= :now')
            ->orderBy('rappel.reminderAt', 'desc')
            ->setParameter('now', date('Y-m-d H:i') . ':00')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Rappel[]
     */
    public function findLastFor(User $user)
    {
        return $this->createQueryBuilder('rappel')
            ->andWhere('rappel.user = :user')
            ->andWhere('rappel.isReminded = true')
            ->orderBy('rappel.reminderAt', 'desc')
            ->setParameter('user', $user)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param User $user
     */
    public function acknowledgeAllFor(User $user): void
    {
        $this->createQueryBuilder('rappel')
            ->update(Rappel::class, 'rappel')
            ->set('rappel.acknowledged', true)
            ->where('rappel.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute()
        ;
    }
}
