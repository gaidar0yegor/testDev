<?php

namespace App\Repository;

use App\Entity\Cra;
use App\Entity\EvenementParticipant;
use App\Entity\SocieteUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvenementParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvenementParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvenementParticipant[]    findAll()
 * @method EvenementParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvenementParticipant::class);
    }

    /**
     * @return EvenementParticipant[] Returns an array of EvenementParticipant objects
     */
    public function findBySocieteUserByMonth(SocieteUser $societeUser, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('evenement_participant')
            ->leftJoin('evenement_participant.evenement', 'evenement', 'WITH', 'YEAR(evenement.startDate) = :year and MONTH(evenement.startDate) = :month')
            ->where('evenement_participant.societeUser = :societeUser')
            ->andWhere('evenement_participant.required = true')
            ->andWhere('evenement.projet is not null')
            ->setParameters([
                'societeUser' => $societeUser,
                'year' => $date->format('Y'),
                'month' => $date->format('m')
            ])
            ->getQuery()
            ->getResult();
    }
}
