<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\User;
use App\File\FileHandler\AvatarHandler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use libphonenumber\PhoneNumber;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User|null findOneByEmail(string $email)
 * @method User|null findOneByTelephone(PhoneNumber $telephone)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private AvatarHandler $avatarHandler;

    public function __construct(ManagerRegistry $registry, AvatarHandler $avatarHandler)
    {
        parent::__construct($registry, User::class);
        $this->avatarHandler = $avatarHandler;
    }

    public function getCountAll(): int
    {
        return $this
            ->createQueryBuilder('user')
            ->select('count(user)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findCreatedAt(int $year): array
    {
        return $this
            ->createQueryBuilder('user')
            ->select('MONTH(user.createdAt) AS mois, count(user) as total')
            ->where('YEAR(user.createdAt) = :year')
            ->setParameter('year', $year)
            ->groupBy('mois')
            ->getQuery()
            ->getResult();
    }

    public function findMentionedUserBySociete(Societe $societe, string $searchQuery): array
    {
        $qb = $this->createQueryBuilder('user');
        $qb
            ->select("CONCAT(user.prenom, ' ', user.nom) AS name, CONCAT(:filesAvatarUri, avatar.nomMd5) AS iconSrc, societeUser.id AS id")
            ->leftJoin('user.societeUsers', 'societeUser')
            ->leftJoin('user.avatar', 'avatar')
            ->leftJoin('societeUser.societe', 'societe')
            ->andWhere('societe.id = :societe')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('user.prenom', ':searchQuery'),
                    $qb->expr()->like('user.nom', ':searchQuery')
                )
            )
            ->setParameters([
                'societe' => $societe->getId(),
                'filesAvatarUri' => $this->avatarHandler->filesAvatarUri,
                'searchQuery' => $searchQuery.'%'
            ]);

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

    public function findByEmailAndSociete(Societe $societe, string $email): ?User
    {
        $qb = $this->createQueryBuilder('user');
        $qb
            ->leftJoin('user.societeUsers', 'societeUser')
            ->leftJoin('societeUser.societe', 'societe')
            ->andWhere('societe = :societe')
            ->andWhere('user.email = :email')
            ->setParameters([
                'societe' => $societe,
                'email' => $email
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findExterneByEmailAndProjet(Projet $projet, string $email): ?User
    {
        $qb = $this->createQueryBuilder('user');
        $qb
            ->leftJoin('user.projetObservateurExternes', 'projetObservateurExterne')
            ->leftJoin('projetObservateurExterne.projet', 'projet')
            ->andWhere('projet = :projet')
            ->andWhere('user.email = :email')
            ->setParameters([
                'projet' => $projet,
                'email' => $email
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByRole(string $role)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->getQuery()
            ->getResult();
    }

    public function updatePatchnoteReaded($readed = false): void
    {
        $this->createQueryBuilder('user')
            ->update(User::class, 'user')
            ->set('user.patchnoteReaded', (int)$readed)
            ->getQuery()
            ->execute()
        ;
    }
}	
