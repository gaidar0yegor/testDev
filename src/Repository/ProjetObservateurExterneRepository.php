<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\ProjetObservateurExterne;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjetObservateurExterne|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjetObservateurExterne|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjetObservateurExterne[]    findAll()
 * @method ProjetObservateurExterne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetObservateurExterneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjetObservateurExterne::class);
    }

    public function findOneByUserAndProjet(User $user, Projet $projet): ?ProjetObservateurExterne
    {
        return $this->findOneBy([
            'user' => $user,
            'projet' => $projet,
        ]);
    }
}
