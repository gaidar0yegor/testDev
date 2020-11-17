<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\User;
use App\Exception\RdiException;
use App\Role;

/**
 * Service avec des fonctions convernant les roles des user sur les projets.
 */
class ParticipantService
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
     * @return bool Si $user a au moins le role $requiredRole sur $projet.
     */
    public function hasRoleOnProjet(User $user, Projet $projet, string $requiredRole): bool
    {
        $userRole = $this->getRoleOfUserOnProjet($user, $projet);

        return $this->hasRole($userRole, $requiredRole);
    }

    public function hasRole(?string $role, string $requiredRole): bool
    {
        if (null === $role) {
            return false;
        }

        $roles = [
            Role::OBSERVATEUR,
            Role::CONTRIBUTEUR,
            Role::CDP,
        ];

        if (!in_array($role, $roles) || !in_array($requiredRole, $roles)) {
            throw new RdiException(sprintf(
                'checkRole() expects $role and $requiredRole be one of: "%s".',
                join('", "', $roles)
            ));
        }

        return array_search($role, $roles) >= array_search($requiredRole, $roles);
    }
}
