<?php

namespace App\Repository;

use App\Entity\FichierProjet;
use App\HasSocieteInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FichiersProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method FichiersProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method FichiersProjet[]    findAll()
 * @method FichiersProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichiersProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FichierProjet::class);
    }
}
