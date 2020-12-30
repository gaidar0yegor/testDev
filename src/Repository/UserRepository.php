<?php

namespace App\Repository;

use App\Entity\Societe;
use App\Entity\User;
use App\Exception\RdiException;
use App\HasSocieteInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function whereSociete(Societe $societe): QueryBuilder
    {
        return $this
            ->createQueryBuilder('user')
            ->where('user.societe = :societe')
            ->setParameter('societe', $societe)
        ;
    }

    public function findBySameSociete(HasSocieteInterface $entity)
    {
        return $this->findBy([
            'societe' => $entity->getSociete(),
        ]);
    }

    public function findWithCra(Societe $societe, int $year)
    {
        return $this->whereSociete($societe)
            ->addSelect('cra')
            ->addSelect('tempsPasse')
            ->leftJoin('user.cras', 'cra', 'WITH', 'user = cra.user and YEAR(cra.mois) = :year')
            ->leftJoin('cra.tempsPasses', 'tempsPasse')
            ->andWhere('user.invitationToken is null')
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
     * @return User[] Liste des utilisateurs auxquels il est possible d'envoyer la notification
     */
    public function findAllNotifiableUsers(Societe $societe, string $notificationSetting)
    {
        if (!preg_match('/^[a-zA-Z]+$/', $notificationSetting)) {
            throw new RdiException(sprintf('"%s" seems not to be a valid field name', $notificationSetting));
        }

        return $this
            ->whereSociete($societe)
            ->andWhere('user.invitationToken is null')
            ->andWhere('user.enabled = true')
            ->andWhere('user.notificationEnabled = true')
            ->andWhere("user.$notificationSetting = true")
            ->getQuery()
            ->getResult()
        ;
    }
}
