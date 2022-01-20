<?php

namespace App\Repository;

use App\Entity\Cra;
use App\Entity\SocieteUser;
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

    public function findCraByUserAndMois(SocieteUser $societeUser, \DateTimeInterface $mois): ?Cra
    {
        return $this->findOneBy([
            'societeUser' => $societeUser,
            'mois' => $mois,
        ]);
    }

    /**
     * @return Cra[]
     */
    public function findCrasByUserAndYear(SocieteUser $societeUser, int $year): array
    {
        return $this->createQueryBuilder('cra')
            ->where('cra.societeUser = :societeUser')
            ->andWhere('YEAR(cra.mois) = :year')
            ->orderBy('cra.mois')
            ->setParameters([
                'societeUser' => $societeUser,
                'year' => $year,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNumberMonthsValidByUserAndYear(SocieteUser $societeUser, int $year): int
    {
        return $this->createQueryBuilder('cra')
            ->select('count(cra.id)')
            ->where('cra.societeUser = :societeUser')
            ->andWhere('YEAR(cra.mois) = :year')
            ->andWhere('cra.tempsPassesModifiedAt is NOT NULL')
            ->setParameters([
                'societeUser' => $societeUser,
                'year' => $year,
            ])
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findValidMoisByUser(SocieteUser $societeUser): array
    {
        $array = $this->createQueryBuilder('cra')
            ->select('cra.mois')
            ->where('cra.societeUser = :societeUser')
            ->andWhere('cra.tempsPassesModifiedAt is NOT NULL')
            ->orderBy('cra.mois', 'asc')
            ->setParameter('societeUser',$societeUser)
            ->getQuery()->getResult();

        return array_column($array, 'mois');
    }
}
