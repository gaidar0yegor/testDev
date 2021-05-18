<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use libphonenumber\PhoneNumber;
use App\Service\DateMonthService;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User|null findOneByEmail(string $email)
 * @method User|null findOneByTelephone(PhoneNumber $telephone)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, DateMonthService $dateMonthService)
    {
        parent::__construct($registry, User::class);

        $this->dateMonthService = $dateMonthService;
    }

    public function findCreatedAt(int $year): array
    {
        return $this
        ->createQueryBuilder('user')
        ->select('MONTH(user.createdAt) AS mois, count(user) as total')
        ->where('YEAR(user.createdAt) = :year')
        ->setParameter('year', $year)
        ->groupBy('mois') 
        ->getQuery()
        ->getResult();
    }
}	