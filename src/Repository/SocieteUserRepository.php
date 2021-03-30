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
     * @param Societe $societe Société dans laquelle envoyer la notification aux utilisateurs
     * @param string $notificationSetting Nom du flag (champ de l'entité User)
     *                                    qui doit être à true pour envoyer la notification.
     *                                    (Utiliser 'notificationEnabled' si pas de champ specifique.)
     *
     * @return SocieteUser[] Liste des utilisateurs auxquels il est possible d'envoyer la notification
     */
    public function findAllNotifiableUsers(Societe $societe, string $notificationSetting): array
    {
        if (!preg_match('/^[a-zA-Z]+$/', $notificationSetting)) {
            throw new InvalidArgumentException(sprintf('"%s" seems not to be a valid field name', $notificationSetting));
        }

        return $this
            ->whereSociete($societe)
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
}
