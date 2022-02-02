<?php

namespace App\Repository;

use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\HasSocieteInterface;
use App\Security\Role\RoleProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

/**
 * @method SocieteUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocieteUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocieteUser|null findOneByInvitationToken(string $token)
 * @method SocieteUser[]    findAll()
 * @method SocieteUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocieteUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocieteUser::class);
    }

    public function whereSociete(Societe $societe): QueryBuilder
    {
        return $this
            ->createQueryBuilder('societeUser')
            ->where('societeUser.societe = :societe')
            ->setParameter('societe', $societe)
        ;
    }

    public function findBySameSociete(HasSocieteInterface $entity)
    {
        return $this->findBy([
            'societe' => $entity->getSociete(),
        ]);
    }

    /**
     * Find all SocieteUser in a Societe with their Cra
     *
     * @return SocieteUser[]
     */
    public function findWithCra(Societe $societe, int $year): array
    {
        return $this->whereSociete($societe)
            ->addSelect('cra')
            ->addSelect('tempsPasse')
            ->leftJoin('societeUser.cras', 'cra', 'WITH', 'societeUser = cra.societeUser and YEAR(cra.mois) = :year')
            ->leftJoin('cra.tempsPasses', 'tempsPasse')
            ->leftJoin('societeUser.user', 'user')
            ->andWhere('societeUser.invitationToken is null')
            ->addOrderBy('user.prenom')
            ->addOrderBy('user.nom')
            ->addOrderBy('cra.mois')
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find SocieteUser of Mon Equipe in a Societe with their Cra
     *
     * @return SocieteUser[]
     */
    public function findWithCraOfMonEquipe(SocieteUser $superior, int $year): array
    {
        $teamMembers = $this->findTeamMembers($superior);

        return $this->whereSociete($superior->getSociete())
            ->addSelect('cra')
            ->addSelect('tempsPasse')
            ->leftJoin('societeUser.cras', 'cra', 'WITH', 'societeUser = cra.societeUser and YEAR(cra.mois) = :year')
            ->leftJoin('cra.tempsPasses', 'tempsPasse')
            ->leftJoin('societeUser.user', 'user')
            ->andWhere('societeUser in (:teamMembers)')
            ->andWhere('societeUser.invitationToken is null')
            ->addOrderBy('user.prenom')
            ->addOrderBy('user.nom')
            ->addOrderBy('cra.mois')
            ->setParameter('year', $year)
            ->setParameter('teamMembers', $teamMembers)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $notificationSetting Nom du flag (champ de l'entité User)
     *                                    qui doit être à true pour envoyer la notification.
     *                                    (Utiliser 'notificationEnabled' si pas de champ specifique.)
     * @param Societe $societe Société dans laquelle envoyer la notification aux utilisateurs
     *
     * @return SocieteUser[] Liste des utilisateurs auxquels il est possible d'envoyer la notification
     */
    public function findAllNotifiableUsers(string $notificationSetting, ?Societe $societe = null): array
    {
        if (!preg_match('/^[a-zA-Z]+$/', $notificationSetting)) {
            throw new InvalidArgumentException(sprintf('"%s" seems not to be a valid field name', $notificationSetting));
        }

        $qb = $this->createQueryBuilder('societeUser');

        if (null !== $societe) {
            $qb = $this->whereSociete($societe);
        }

        return $qb
            ->leftJoin('societeUser.user', 'user')
            ->andWhere('societeUser.invitationToken is null')
            ->andWhere('societeUser.enabled = true')
            ->andWhere('user.enabled = true')
            ->andWhere('user.notificationEnabled = true')
            ->andWhere("user.$notificationSetting = true")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Retourne les SocieteUser toujours en cours d'invitation,
     * et qui n'ont pas refusé une catégorie de mails (i.e: onboarding).
     *
     * @param string $notificationSetting Nom du flag (champ de l'entité SocieteUser)
     *                                    qui doit être à true pour envoyer la notification.
     * @param Societe $societe Société dans laquelle envoyer la notification aux utilisateurs
     *
     * @return SocieteUser[] Liste des utilisateurs auxquels il est possible d'envoyer la notification
     */
    public function findAllNotifiableUsersNotYetJoined(string $notificationSetting, ?Societe $societe = null): array
    {
        if (!preg_match('/^[a-zA-Z]+$/', $notificationSetting)) {
            throw new InvalidArgumentException(sprintf('"%s" seems not to be a valid field name', $notificationSetting));
        }

        $qb = $this->createQueryBuilder('societeUser');

        if (null !== $societe) {
            $qb = $this->whereSociete($societe);
        }

        return $qb
            ->andWhere('societeUser.invitationToken is not null')
            ->andWhere('societeUser.user is null')
            ->andWhere('societeUser.enabled = true')
            ->andWhere("societeUser.$notificationSetting = true")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find all active users having the role $role or more at least once on any projet of $societe.
     *
     * @return SocieteUser[]
     */
    public function findUsersWithAtLeastOneRoleOnProjets(Societe $societe, string $role): array
    {
        return $this->createQueryBuilder('societeUser')
            ->leftJoin('societeUser.projetParticipants', 'projetParticipant')
            ->leftJoin('projetParticipant.projet', 'projet')
            ->where('projet.societe = :societe')
            ->andWhere('projetParticipant.role in (:role)')
            ->andWhere('societeUser.enabled = true')
            ->setParameters([
                'societe' => $societe,
                'role' => RoleProjet::getRoles($role),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryBuilderTeamMembers(SocieteUser $societeUser): QueryBuilder
    {
        $qb = $this->createQueryBuilder('societeUser');
        return $qb->leftJoin('societeUser.mySuperior', 'mySuperior')
            ->where('societeUser = :superior')
            ->orWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('societeUser.mySuperior', ':superior'),
                    $qb->expr()->in(
                        'mySuperior.id',
                        $this->createQueryBuilder('societeUserN_1')
                            ->select('societeUserN_1.id')
                            ->where('societeUserN_1.mySuperior = :superior')
                            ->getDQL()
                    )
                )
            )
            ->setParameter('superior', $societeUser);
    }

    /**
     * Find all my hierarchical links : N-1 et N-2
     *
     * @return SocieteUser[]
     */
    public function findTeamMembers(SocieteUser $societeUser): array
    {
        return $this
            ->queryBuilderTeamMembers($societeUser)
            ->getQuery()
            ->getResult();
    }
}
