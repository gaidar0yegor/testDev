<?php

namespace App\Repository;


use App\Entity\EvenementParticipant;
use App\Entity\SocieteUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    /**
     * @return EvenementParticipant[] Returns an array of EvenementParticipant objects
     */
    public function checkOverlapEvenements(\DateTime $startDate, \DateTime $endDate, array $societeUserIds, int $exceptEventId = null): array
    {
        $qb = $this->createQueryBuilder('evenement_participant');
        return $qb
            ->leftJoin('evenement_participant.evenement', 'evenement')
            ->leftJoin('evenement_participant.societeUser', 'societeUser')
            ->andWhere('evenement.id != :exceptEventId')
            ->andWhere('societeUser.id in (:societeUserIds)')
            ->andWhere('evenement_participant.required = true')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->between('evenement.startDate', ':start', ':end'),
                        $qb->expr()->neq('evenement.startDate', ':end'),
                        ),
                    $qb->expr()->andX(
                        $qb->expr()->between('evenement.endDate', ':start', ':end'),
                        $qb->expr()->neq('evenement.endDate', ':start'),
                        ),
                    $qb->expr()->andX(
                        $qb->expr()->between(':start', 'evenement.startDate', 'evenement.endDate'),
                        $qb->expr()->neq(':start', 'evenement.endDate'),
                        ),
                    $qb->expr()->andX(
                        $qb->expr()->between(':end', 'evenement.startDate', 'evenement.endDate'),
                        $qb->expr()->neq(':end', 'evenement.startDate'),
                        ),
                    )
            )
            ->setParameters([
                'societeUserIds' => $societeUserIds,
                'exceptEventId' => $exceptEventId ?? 0,
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s')
            ])
            ->getQuery()
            ->getResult();
    }
}
