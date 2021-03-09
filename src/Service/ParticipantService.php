<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\User;
use App\Exception\RdiException;
use App\Role;
use App\Service\Timesheet\UserContributingProjetRepositoryInterface;

/**
 * Service avec des fonctions convernant les roles des user sur les projets.
 */
class ParticipantService implements UserContributingProjetRepositoryInterface
{
    /**
     * @return bool Si oui ou non $user est au moins observateur sur $projet
     */
    public function isParticipant(User $user, Projet $projet): bool
    {
        return null !== $this->getRoleOfUserOnProjet($user, $projet);
    }

    /**
     * @return null|string Role de $user sur $projet.
     *      Either 'CDP', 'CONTRIBUTEUR' or 'OBSERVATEUR'.
     *      Returns null if user has no access to $projet.
     */
    public function getRoleOfUserOnProjet(User $user, Projet $projet): ?string
    {
        foreach ($projet->getProjetParticipants() as $participant) {
            if ($participant->getUser() === $user) {
                return $participant->getRole();
            }
        }

        return null;
    }

    /**
     * @return ProjetParticipant[] Tous les projetParticipants de $user
     *      qui ont au moins le rôle $requiredRole (ou plus).
     */
    public function getProjetParticipantsWithRole(iterable $projetParticipants, string $requiredRole): iterable
    {
        $projetParticipantsFiltered = [];

        foreach ($projetParticipants as $projetParticipant) {
            if ($this->hasRole($projetParticipant->getRole(), $requiredRole)) {
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
    public function findProjetsContributingUser(User $user): array
    {
        return $this->getProjetParticipantsWithRole(
            $user->getProjetParticipants(),
            Role::CONTRIBUTEUR
        );
    }

    /**
     * @return bool Si $user a au moins le role $requiredRole sur $projet.
     */
    public function hasRoleOnProjet(User $user, Projet $projet, string $requiredRole): bool
    {
        $userRole = $this->getRoleOfUserOnProjet($user, $projet);

        return $this->hasRole($userRole, $requiredRole);
    }

    /**
     * @deprecated Use Role::hasRole() instead.
     */
    public function hasRole(?string $role, string $requiredRole): bool
    {
        if (null === $role) {
            return false;
        }

        return Role::hasRole($role, $requiredRole);
    }
}
