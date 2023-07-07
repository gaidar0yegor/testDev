<?php

namespace App\Repository;

use App\Entity\FichierProjet;
use App\Entity\Societe;
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

    public function whereSociete(Societe $societe)
    {
        return $this
            ->createQueryBuilder('fichierProjet')
            ->leftJoin('fichierProjet.uploadedBy', 'user')
            ->where('user.societe = :societe')
            ->setParameters([
                'societe' => $societe,
            ])
        ;
    }

    public function whereCanBeLinkedToFaitMarquant(Societe $societe)
    {
        return $this
            ->whereSociete($societe)
            ->andWhere('fichierProjet.faitMarquant is null')
        ;
    }
}
