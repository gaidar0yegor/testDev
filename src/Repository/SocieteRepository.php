<?php

namespace App\Repository;

use App\Entity\Societe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Societe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Societe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Societe[]    findAll()
 * @method Societe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocieteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Societe::class);
    }

    public function getCountAll(): int
    {
        return $this
            ->createQueryBuilder('societe')
            ->select('count(societe)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findCreatedAt(int $year): array
    {
        return $this
            ->createQueryBuilder('societe')
            ->select('MONTH(societe.createdAt) AS mois, count(societe) as total')
            ->where('YEAR(societe.createdAt) = :year')
            ->setParameter('year', $year)
            ->groupBy('mois')
            ->getQuery()
            ->getResult();
    }

    /*
     * Mettre en veille les société désactivées automatiquement après X mois
     */
    public function findToMettreEnVeille(int $nbrMonths): array
    {
        $date = (new \DateTime())->modify("-". $nbrMonths ." month");

        return $this
            ->createQueryBuilder('societe')
            ->where('societe.disabledAt <= :date')
            ->andWhere('societe.enabled = false')
            ->andWhere('societe.onStandBy = false')
            ->setParameter('date', $date->format('Y-m-d') . ' 00:00:00')
            ->getQuery()
            ->getResult();
    }

    // public function findRaisonSociale()
    // {
    //     return $this
    //         ->createQueryBuilder('societe')
    //         ->select('societe.raisonSociale')
    //         ->getQuery()
    //         ->getResult();
    // }
}
