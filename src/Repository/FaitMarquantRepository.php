<?php

namespace App\Repository;

use App\Entity\FaitMarquant;
use App\Entity\User;
use App\Role;
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

    public function findLatestOnUserProjets(User $user, DateTimeInterface $from, string $minimumRole = Role::OBSERVATEUR)
    {
        return $this->createQueryBuilder('faitMarquant')
            ->leftJoin('faitMarquant.projet', 'projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->leftJoin('projetParticipant.user', 'user')
            ->andWhere('faitMarquant.date >= :from')
            ->andWhere('projetParticipant.role in (:roles)')
            ->andWhere('projetParticipant.user = :user')
            ->addOrderBy('projet.acronyme', 'asc')
            ->addOrderBy('faitMarquant.date', 'desc')
            ->setParameters([
                'user' => $user,
                'from' => $from,
                'roles' => Role::getRoles($minimumRole),
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
