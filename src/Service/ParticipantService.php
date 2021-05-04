<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Security\Role\RoleProjet;
use App\Service\Timesheet\UserContributingProjetRepositoryInterface;
use Traversable;

/**
 * Service avec des fonctions convernant les roles des user sur les projets.
 */
class ParticipantService implements UserContributingProjetRepositoryInterface
{
    /**
     * Get ProjetParticipant from a SocieteUser and a Projet.
     */
    public function getProjetParticipant(SocieteUser $societeUser, Projet $projet): ?ProjetParticipant
    {
        foreach ($projet->getProjetParticipants() as $participant) {
            if ($participant->getSocieteUser() === $societeUser) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * @return bool Si oui ou non $societeUser est au moins observateur sur $projet
     */
    public function isParticipant(SocieteUser $societeUser, Projet $projet): bool
    {
        return null !== $this->getProjetParticipant($societeUser, $projet);
    }

    /**
     * @return null|string Role de $societeUser sur $projet.
     *      Either RoleProjet::CDP, RoleProjet::CONTRIBUTEUR or RoleProjet::OBSERVATEUR.
     *      Returns null if user has no access to $projet.
     */
    public function getRoleOfUserOnProjet(SocieteUser $societeUser, Projet $projet): ?string
    {
        $projetParticipant = $this->getProjetParticipant($societeUser, $projet);

        if (null === $projetParticipant) {
            return null;
        }

        return $projetParticipant->getRole();
    }

    /**
     * @return ProjetParticipant[] Tous les projetParticipants de $user
     *      qui ont au moins le rôle $requiredRole (ou plus).
     */
    public function getProjetParticipantsWithRole(iterable $projetParticipants, string $requiredRole): iterable
    {
        $projetParticipantsFiltered = [];

        foreach ($projetParticipants as $projetParticipant) {
            if (RoleProjet::hasRole($projetParticipant->getRole(), $requiredRole)) {
                $projetParticipantsFiltered[] = $projetParticipant;
            }
        }

        return $projetParticipantsFiltered;
    }

    /**
     * @return ProjetParticipant[] Tous les projetParticipants de $user
     *      qui ont exactement le role $role
     */
    public function getProjetParticipantsWithRoleExactly(iterable $projetParticipants, string $role): iterable
    {
        $projetParticipantsFiltered = [];

        foreach ($projetParticipants as $projetParticipant) {
            if ($projetParticipant->getRole() === $role) {
                $projetParticipantsFiltered[] = $projetParticipant;
            }
        }

        return $projetParticipantsFiltered;
    }

    /**
     * {@inheritDoc}
     */
    public function findProjetsContributingUser(SocieteUser $societeUser): array
    {
        return $this->getProjetParticipantsWithRole(
            $societeUser->getProjetParticipants(),
            RoleProjet::CONTRIBUTEUR
        );
    }

    /**
     * @return bool Si $user a au moins le role $requiredRole sur $projet.
     */
    public function hasRoleOnProjet(SocieteUser $societeUser, Projet $projet, string $requiredRole): bool
    {
        $userRole = $this->getRoleOfUserOnProjet($societeUser, $projet);

        if (null === $userRole) {
            return false;
        }

        return RoleProjet::hasRole($userRole, $requiredRole);
    }

    /**
     * Sort projetParticipants list by role (Chef de projet, then Contributeur, then Observateur)
     *
     * @param iterable $projetParticipants
     * @param string $order "asc" or "desc"
     *
     * @return ProjetParticipant[]
     */
    public function sortByRole(iterable $projetParticipants, string $ascOrDesc = 'desc'): array
    {
        $roleOrder = [
            RoleProjet::CDP => 3,
            RoleProjet::CONTRIBUTEUR => 2,
            RoleProjet::OBSERVATEUR => 1,
        ];

        $order = 'desc' === strtolower($ascOrDesc) ? 1 : -1;
        $sorted = ($projetParticipants instanceof Traversable) ? iterator_to_array($projetParticipants) : (array) $projetParticipants;

        usort(
            $sorted,
            function (ProjetParticipant $a, ProjetParticipant $b) use ($order, $roleOrder) {
                return ($roleOrder[$b->getRole()] - $roleOrder[$a->getRole()]) * $order;
            }
        );

        return $sorted;
    }
}
