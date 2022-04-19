<?php

namespace App\Repository;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Security\Role\RoleProjet;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FaitMarquant|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaitMarquant|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaitMarquant[]    findAll()
 * @method FaitMarquant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaitMarquantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaitMarquant::class);
    }

    public function findLatestOnUserProjets(SocieteUser $societeUser, DateTimeInterface $from, string $minimumRole = RoleProjet::OBSERVATEUR)
    {
        RoleProjet::checkRole($minimumRole);

        return $this->createQueryBuilder('faitMarquant')
            ->leftJoin('faitMarquant.projet', 'projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->andWhere('faitMarquant.trashedAt is null')
            ->andWhere('faitMarquant.date >= :from')
            ->andWhere('projetParticipant.role in (:roles)')
            ->andWhere('projetParticipant.societeUser = :societeUser')
            ->addOrderBy('projet.acronyme', 'asc')
            ->addOrderBy('faitMarquant.date', 'desc')
            ->setParameters([
                'societeUser' => $societeUser,
                'from' => $from,
                'roles' => RoleProjet::getRoles($minimumRole),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTrashItems(Projet $projet)
    {
        return $this->createQueryBuilder('faitMarquant')
            ->where('faitMarquant.trashedAt is not null')
            ->andWhere('faitMarquant.projet = :projet')
            ->orderBy('faitMarquant.trashedAt', 'desc')
            ->setParameters([
                'projet' => $projet,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
