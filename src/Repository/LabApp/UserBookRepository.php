<?php

namespace App\Repository\LabApp;

use App\Entity\LabApp\Labo;
use App\Entity\LabApp\UserBook;
use App\Entity\Societe;
use App\HasSocieteInterface;
use App\HasUserBookInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserBook|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBook|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBook|null findOneByInvitationToken(string $token)
 * @method UserBook[]    findAll()
 * @method UserBook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBook::class);
    }

    public function whereLabo(Labo $labo): QueryBuilder
    {
        return $this
            ->createQueryBuilder('userBook')
            ->where('userBook.labo = :labo')
            ->setParameter('labo', $labo)
            ;
    }

    public function findBySameLabo(Labo $labo)
    {
        return $this->findBy([
            'labo' => $labo,
        ]);
    }
}
