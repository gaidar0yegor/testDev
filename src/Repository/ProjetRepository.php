<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Security\Role\RoleProjet;
use App\Service\DateMonthService;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function getCountAll(): int
    {
        return $this
            ->createQueryBuilder('projet')
            ->select('count(projet)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function whereUserAndRole(SocieteUser $societeUser, ?string $roleMinimum = null)
    {
        $qb = $this
            ->createQueryBuilder('projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->leftJoin('projetParticipant.societeUser', 'societeUser')
            ->where('projetParticipant.societeUser = :societeUser')
            ->setParameter('societeUser', $societeUser)
        ;

        if (null !== $roleMinimum) {
            $qb
                ->andWhere('projetParticipant.role in (:roles)')
                ->setParameter('roles', RoleProjet::getRoles($roleMinimum))
            ;
        }

        return $qb;
    }

    /**
     * @return Projet[]
     */
    public function findActiveProjetForSociete(Societe $societe): array
    {
        return $this
            ->createQueryBuilder('projet')
            ->where('projet.societe = :societe')
            ->andWhere('projet.dateDebut is null OR :now >= projet.dateDebut')
            ->andWhere('projet.dateFin is null OR :now <= projet.dateFin')
            ->setParameters([
                'societe' => $societe,
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Recupère tous les projet auxquels $societeUser participe.
     *
     * @param SocieteUser $societeUser
     * @param null|string $roleMinimum Rôle minimum sur le projet
     * @param null|\DateTime $month Mois pour lequel les projets doivent êtres actifs
     *
     * @return Projet[]
     */
    public function findAllForUser(SocieteUser $societeUser, ?string $roleMinimum = null, \DateTime $month = null): array
    {
        $qb = $this->whereUserAndRole($societeUser, $roleMinimum);

        if (null !== $month) {
            $month = $this->dateMonthService->normalize($month);
            $nextMonth = $this->dateMonthService->getNextMonth($month);

            $qb
                ->andWhere('projet.dateDebut is null OR :nextMonth > projet.dateDebut')
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
     * Retourne l'année minimum et l'année maximum auxquelles il existe des projets actifs.
     *
     * @return null|int[] Array with values: ["yearMin" => xxxx, "yearMax" => xxxx], or null if no projets.
     */
    public function findProjetsYearRangeFor(SocieteUser $societeUser, ?string $roleMinimum = null): ?array
    {
        $qb = $this->whereUserAndRole($societeUser, $roleMinimum);

        $qb
            ->select('min(year(projet.dateDebut)) as yearMin')
            ->addSelect('max(year(projet.dateFin)) as yearMax')
        ;

        $result = $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $result['yearMin']) {
            return null;
        }

        return array_map('intval', $result);
    }

    /**
     * Recupère tous les projet auxquels une collection de societeUsers participent.
     *
     * @param SocieteUser[] $societeUsers
     *
     * @return Projet[]
     */
    public function findAllForUsers(array $societeUsers, int $sinceYear = null, int $toYear = null): array
    {
        $qb =  $this
            ->createQueryBuilder('projet')
            ->leftJoin('projet.projetParticipants','projetParticipant')
            ->leftJoin('projetParticipant.societeUser', 'societeUser')
            ->where('societeUser.id in (:societeUsers)')
            ->setParameter('societeUsers',$societeUsers);

        if (null !== $sinceYear) {
            $qb
                ->andWhere('projet.dateFin is null or projet.dateFin >= :year')
                ->setParameter('year', new \DateTime("$sinceYear-01-01"))
            ;

            if (null === $toYear) {
                $toYear = date('Y');
            }

            $qb
                ->andWhere('projet.dateDebut is null or projet.dateDebut <= :currentYear')
                ->setParameter('currentYear', new \DateTime("$toYear-12-31"))
            ;
        }

        $projets = $qb->getQuery()->getResult();

        return $projets;
    }

    /**
     * Recupère tous les projet auxquels les n-1 et les n-2 de $societeUser participent.
     *
     * @param SocieteUser $societeUser
     * @param null|string $roleMinimum Rôle minimum sur le projet
     * @param null|int $year Année pour laquelle les projets doivent êtres actifs
     *
     * @return Projet[]
     */
    public function findAllForUserInYear(SocieteUser $societeUser, ?string $roleMinimum = null, ?int $year = null): array
    {
        $qb = $this->whereUserAndRole($societeUser, $roleMinimum);

        if (null !== $year) {
            $qb
                ->andWhere('projet.dateDebut is null or projet.dateDebut <= :yearEnd')
                ->andWhere('projet.dateFin is null or projet.dateFin >= :yearStart')
                ->setParameter('yearStart', new \DateTime("$year-01-01"))
                ->setParameter('yearEnd', new \DateTime("$year-12-31"))
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Recupère tous les projet auxquels $societeUser participe depuis une année jusque maintenant.
     *
     * @param SocieteUser $societeUser
     * @param null|string $roleMinimum Rôle minimum sur le projet
     * @param null|int $year Année à partir de laquelle les projets doivent êtres actifs
     *
     * @return Projet[]
     */
    public function findAllForUserSinceYear(SocieteUser $societeUser, ?string $roleMinimum = null, ?int $year = null): array
    {
        $qb = $this->whereUserAndRole($societeUser, $roleMinimum);

        if (null !== $year) {
            $qb
                ->andWhere('projet.dateFin is null or projet.dateFin >= :year')
                ->andWhere('projet.dateDebut is null or projet.dateDebut <= :currentYear')
                ->setParameter('year', new \DateTime("$year-01-01"))
                ->setParameter('currentYear', new \DateTime(date('Y').'-12-31'))
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
     * @param Societe $societe
     * @param int $sinceYear Ne prend que les projets en cours entre $sinceYear et maintenant
     *
     * @return Projet[]
     */
    public function findAllProjectsPerSociete(Societe $societe, int $sinceYear = null, int $toYear = null): array
    {
        $qb = $this
            ->createQueryBuilder('projet')
            ->where('projet.societe = :societe')
            ->setParameter('societe', $societe)
        ;

        if (null !== $sinceYear) {
            $qb
                ->andWhere('projet.dateFin is null or projet.dateFin >= :year')
                ->setParameter('year', new \DateTime("$sinceYear-01-01"))
            ;

            if (null === $toYear) {
                $toYear = date('Y');
            }

            $qb
                ->andWhere('projet.dateDebut is null or projet.dateDebut <= :currentYear')
                ->setParameter('currentYear', new \DateTime("$toYear-12-31"))
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns recently used projet for an user,
     * based on ProjetParticipant::lastActionAt.
     *
     * @return Projet[]
     */
    public function findRecentsForUser(SocieteUser $societeUser, int $limit = 2): array
    {
        return $this
            ->createQueryBuilder('projet')
            ->leftJoin('projet.projetParticipants', 'projetParticipant')
            ->where('projetParticipant.societeUser = :societeUser')
            ->andWhere('projetParticipant.lastActionAt is not null')
            ->orderBy('projetParticipant.lastActionAt', 'desc')
            ->setMaxResults($limit)
            ->setParameters([
                'societeUser' => $societeUser,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Projet[]
     */
    public function findProjetsWhereUserHasNoRole(SocieteUser $societeUser): array
    {
        $userProjetsQuery = $this
            ->whereUserAndRole($societeUser)
            ->select('projet.id')
        ;

        $queryBuilder = $this->createQueryBuilder('projet2');

        $queryBuilder = $queryBuilder
            ->where('projet2.societe = :societe')
            ->andWhere($queryBuilder->expr()->notIn('projet2.id', $userProjetsQuery->getDQL()))
            ->setParameters($userProjetsQuery->getParameters())
            ->setParameter('societe', $societeUser->getSociete())
        ;


        return $queryBuilder
                ->getQuery()
                ->getResult()
        ;
    }

    public function findCreatedAt(int $year): array
    {
        return $this
        ->createQueryBuilder('projet')
        ->select('MONTH(projet.createdAt) AS mois, count(projet) as total')
        ->where('YEAR(projet.createdAt) = :year')
        ->setParameter('year', $year)
        ->groupBy('mois') 
        ->getQuery()
        ->getResult();
    }
}
