<?php

namespace App\Repository;

use App\Entity\Cra;
use App\Entity\User;
use App\Service\Timesheet\UserMonthCraRepositoryInterface;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cra|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cra|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cra[]    findAll()
 * @method Cra[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CraRepository extends ServiceEntityRepository implements UserMonthCraRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cra::class);
    }

    public function findCraByUserAndMois(User $user, \DateTimeInterface $mois): ?Cra
    {
        return $this->findOneBy([
            'user' => $user,
            'mois' => $mois,
        ]);
    }

    /**
     * @return Cra[]
     */
    public function findCrasByUserAndYear(User $user, int $year): array
    {
        return $this->createQueryBuilder('cra')
            ->where('cra.user = :user')
            ->andWhere('YEAR(cra.mois) = :year')
            ->orderBy('cra.mois')
            ->setParameters([
                'user' => $user,
                'year' => $year,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
