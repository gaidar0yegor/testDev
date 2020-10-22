<?php

namespace App\Repository;

use App\Entity\TempsPasse;
use App\Entity\User;
use App\Service\DateMonthService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TempsPasse|null find($id, $lockMode = null, $lockVersion = null)
 * @method TempsPasse|null findOneBy(array $criteria, array $orderBy = null)
 * @method TempsPasse[]    findAll()
 * @method TempsPasse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TempsPasseRepository extends ServiceEntityRepository
{
    private $dateMonthService;

    public function __construct(ManagerRegistry $registry, DateMonthService $dateMonthService)
    {
        parent::__construct($registry, TempsPasse::class);

        $this->dateMonthService = $dateMonthService;
    }

    public function findAllForUserAndMonth(User $user, \DateTime $mois): array
    {
        $this->dateMonthService->normalize($mois);

        return $this
            ->createQueryBuilder('tempsPasse')
            ->where('tempsPasse.user = :user')
            ->andWhere('tempsPasse.mois = :mois')
            ->setParameters([
                'user' => $user,
                'mois' => $mois,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
