<?php

namespace App\Repository;

use App\Entity\Societe;
use App\Entity\User;
use App\HasSocieteInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function whereSociete(Societe $societe): QueryBuilder
    {
        return $this
            ->createQueryBuilder('user')
            ->where('user.societe = :societe')
            ->setParameter('societe', $societe)
        ;
    }

    public function findBySameSociete(HasSocieteInterface $entity)
    {
        return $this->findBy([
            'societe' => $entity->getSociete(),
        ]);
    }
}
