<?php

namespace App\Repository;

use App\Entity\SlackAccessToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SlackAccessToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method SlackAccessToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method SlackAccessToken[]    findAll()
 * @method SlackAccessToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SlackAccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SlackAccessToken::class);
    }
}
