<?php

namespace App\Repository;

use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\User;
use App\Security\Role\RoleProjet;
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
     * Find all projet participant for a $societeUser by $role.
     *
     * @return ProjetParticipant[]
     */
    public function findBySocieteUserAndRole(SocieteUser $societeUser, string $role): array
    {
        return $this->createQueryBuilder('projetParticipant')
            ->leftJoin('projetParticipant.societeUser', 'societeUser')
            ->where('societeUser = :societeUser')
            ->andWhere('projetParticipant.role = :role')
            ->setParameters([
                'societeUser' => $societeUser,
                'role' => $role,
            ])
            ->getQuery()
            ->getResult()
            ;
    }
}
