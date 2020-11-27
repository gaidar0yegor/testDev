<?php

namespace App\Repository;

use App\Entity\ProjetParticipant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetParticipant[]    findAll()
 * @method ProjetParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetParticipant::class);
    }

    /**
     * Recupère tous les projet auxquels $user participe.
     */
    public function findAllForUser(User $user): array
    {
        return $this
            ->createQueryBuilder('projetParticipant')
            ->where('projetParticipant.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Recupère tous les projet pour lesquels tous les users d'une société donnée participent.
     */
    public function findAllForUsersOfSociete(User $user): array
    {
        return $this
            ->createQueryBuilder('projetParticipant')
            ->where('projetParticipant.user = :user')  //!!!!!
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

}
