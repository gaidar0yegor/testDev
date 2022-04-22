<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\TempsPasse;
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

    public function findAllForUserInYear(SocieteUser $societeUser, int $year): array
    {
        return $this
            ->createQueryBuilder('tempsPasse')
            ->leftJoin('tempsPasse.cra', 'cra')
            ->where('cra.societeUser = :societeUser')
            ->andWhere('cra.mois >= :dateFrom')
            ->andWhere('cra.mois <= :dateTo')
            ->setParameters([
                'societeUser' => $societeUser,
                'dateFrom' => new \DateTime("$year-01-01"),
                'dateTo' => new \DateTime("$year-12-31"),
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
            ->leftJoin('cra.societeUser', 'societeUser')
            ->where('societeUser.societe = :societe')
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

    /**
     * @return TempsPasse[]
     */
    public function findAllByProjetAndYear(Projet $projet, int $year): array
    {
        return $this
            ->createQueryBuilder('tempsPasse')
            ->leftJoin('tempsPasse.cra', 'cra')
            ->leftJoin('cra.societeUser', 'societeUser')
            ->addSelect('cra')
            ->addSelect('societeUser')
            ->where('YEAR(cra.mois) = :year')
            ->andWhere('tempsPasse.projet = :projet')
            ->setParameters([
                'projet' => $projet,
                'year' => $year,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TempsPasse[]
     */
    public function findAllByProjetAndUser(Projet $projet, SocieteUser $societeUser): array
    {
        return $this
            ->createQueryBuilder('tempsPasse')
            ->leftJoin('tempsPasse.cra', 'cra')
            ->leftJoin('cra.societeUser', 'societeUser')
            ->addSelect('cra')
            ->andWhere('societeUser = :societeUser')
            ->andWhere('tempsPasse.projet = :projet')
            ->setParameters([
                'projet' => $projet,
                'societeUser' => $societeUser,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
