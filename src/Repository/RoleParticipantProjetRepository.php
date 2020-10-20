<?php

namespace App\Repository;

use App\Entity\RoleParticipantProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RoleParticipantProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoleParticipantProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoleParticipantProjet[]    findAll()
 * @method RoleParticipantProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleParticipantProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleParticipantProjet::class);
    }
}
