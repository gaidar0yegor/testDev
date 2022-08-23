<?php

namespace App\Repository;

use App\Entity\Patchnote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Patchnote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Patchnote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Patchnote[]    findAll()
 * @method Patchnote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PatchnoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patchnote::class);
    }

}
