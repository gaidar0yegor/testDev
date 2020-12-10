<?php

namespace App\Repository;

use App\HasSocieteInterface;
use Cron\CronBundle\Entity\CronJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CronJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method CronJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method CronJob[]    findAll()
 * @method CronJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CronJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CronJob::class);
    }

    public function findAllSameSociete(HasSocieteInterface $entity)
    {
        return $this->createQueryBuilder('cronJob')
            ->where('cronJob.name like :pattern')
            ->setParameters([
                'pattern' => '%-societe-'.$entity->getSociete()->getId(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
