<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function getByCreteria(string $type, array $parameters = null, \DateTime $minDateTime = null,  \DateTime $maxDateTime = null)
    {
        $qb = $this->createQueryBuilder('activity')
            ->andWhere('activity.type = :type')
            ->setParameter('type', $type);

        if (null !== $parameters){
            $qb->andWhere('activity.parameters = :parameters')
                ->setParameter('parameters', json_encode($parameters));
        }

        if (null !== $minDateTime){
            $qb->andWhere('activity.datetime >= :minDateTime')
                ->setParameter('minDateTime', $minDateTime->format('Y-m-d H:i:s'));
        }

        if (null !== $maxDateTime){
            $qb->andWhere('activity.datetime <= :maxDateTime')
                ->setParameter('maxDateTime', $maxDateTime->format('Y-m-d H:i:s'));
        }

        return $qb->getQuery()->getResult();
    }
}
