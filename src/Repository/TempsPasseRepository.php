<?php

namespace App\Repository;

use App\Entity\Societe;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\HasSocieteInterface;
use App\Service\DateMonthService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TempsPasse|null find($id, $lockMode = null, $lockVersion = null)
 * @method TempsPasse|null findOneBy(array $criteria, array $orderBy = null)
 * @method TempsPasse[]    findAll()
 * @method TempsPasse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TempsPasseRepository extends ServiceEntityRepository
{
    private $dateMonthService;

    public function __construct(ManagerRegistry $registry, DateMonthService $dateMonthService)
    {
        parent::__construct($registry, TempsPasse::class);

        $this->dateMonthService = $dateMonthService;
    }

    public function findAllForUserAndMonth(User $user, \DateTime $mois): array
    {
        return $this
            ->createQueryBuilder('tempsPasse')
            ->where('tempsPasse.user = :user')
            ->andWhere('tempsPasse.mois = :mois')
            ->setParameters([
                'user' => $user,
                'mois' => $this->dateMonthService->normalize($mois),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TempsPasse[]
     */
    public function findAllBySocieteInYear(Societe $societe, int $year): array
    {
        return $this
            ->createQueryBuilder('tempsPasse')
            ->leftJoin('tempsPasse.cra', 'cra')
            ->leftJoin('cra.user', 'user')
            ->where('user.societe = :societe')
            ->andWhere('cra.mois >= :dateFrom')
            ->andWhere('cra.mois <= :dateTo')
            ->setParameters([
                'societe' => $societe,
                'dateFrom' => new \DateTime("$year-01-01"),
                'dateTo' => new \DateTime("$year-12-31"),
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
