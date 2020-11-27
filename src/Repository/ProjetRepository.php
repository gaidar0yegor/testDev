<?php

namespace App\Repository;

use App\Role;
use App\Entity\User;
use App\Entity\Projet;
use App\Entity\Societe;
use App\Service\DateMonthService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    private $dateMonthService;

    public function __construct(ManagerRegistry $registry, DateMonthService $dateMonthService)
    {
        parent::__construct($registry, Projet::class);

        $this->dateMonthService = $dateMonthService;
    }

    /**
     * Recupère tous les projet auxquels $user participe.
     *
     * @param User $user
     * @param null|string $roleMinimum Rôle minimum sur le projet
     * @param null|\DateTime $month Mois pour lequel les projets doivent êtres actifs
     *
     * @return Projet[]
     */
    public function findAllForUser(User $user, ?string $roleMinimum = null, \DateTime $month = null): array
    {
        $qb = $this
            ->createQueryBuilder('projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->leftJoin('projetParticipant.user', 'user')
            ->where('projetParticipant.user = :user')
        ;

        if (null !== $roleMinimum) {
            $qb
                ->andWhere('projetParticipant.role in (:roles)')
                ->setParameter('user', $user)
                ->setParameter('roles', Role::getRoles($roleMinimum))
            ;
        }

        if (null !== $month) {
            $month = $this->dateMonthService->normalize($month);
            $nextMonth = $this->dateMonthService->getNextMonth($month);

            $qb
                ->andWhere('projet.dateDebut is null OR :nextMonth >= projet.dateDebut')
                ->andWhere('projet.dateFin is null OR :currentMonth <= projet.dateFin')
                ->setParameter('nextMonth', $nextMonth)
                ->setParameter('currentMonth', $month)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère tous les projets d'une même societe.
     *
     * @return Projet[]
     */
    public function findAllProjectsPerSociete( Societe $societe ): array
    {

        return $this
            ->createQueryBuilder('projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->leftJoin('projetParticipant.user', 'user')
            ->where('user.societe = :societe')
            ->setParameter('societe', $societe)
            ->getQuery()
            ->getResult()
        ;
    }


}
