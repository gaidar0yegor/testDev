<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\User;
use App\Service\DateMonthService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    private $dateMonthService;

    public function __construct(ManagerRegistry $registry, DateMonthService $dateMonthService)
    {
        parent::__construct($registry, Projet::class);

        $this->dateMonthService = $dateMonthService;
    }

    /**
     * Recupère tous les projet auxquels $user participe.
     */
    public function findAllForUser(User $user): array
    {
        return $this
            ->createQueryBuilder('projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->leftJoin('projetParticipant.user', 'user')
            ->where('projetParticipant.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère les temps passés sur les projets auxquels participe $user, sur le $mois.
     *
     * @return Projet[]
     */
    public function findTempsPassesForUserAndMonth(User $user, \DateTime $mois): array
    {
        $this->dateMonthService->normalize($mois);

        return $this
            ->createQueryBuilder('projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->leftJoin('projetParticipant.user', 'user')
            ->leftJoin('user.tempsPasses', 'tempsPasse', 'with', 'tempsPasse.projet = projet')
            ->where('projetParticipant.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}