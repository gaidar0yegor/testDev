<?php

namespace App\Security\Voter;

use App\Entity\Projet;
use App\Entity\User;
use App\Role;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * peut effectuer une action sur un projet donné.
 */
class ProjetVoter extends Voter
{
    private $authChecker;

    private $participantService;

    private $societeChecker;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        ParticipantService $participantService,
        SocieteChecker $societeChecker
    ) {
        $this->authChecker = $authChecker;
        $this->participantService = $participantService;
        $this->societeChecker = $societeChecker;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Projet && in_array($attribute, [
            'view',
            'edit',
            'delete',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->userCan($attribute, $token->getUser(), $subject);
    }

    private function userCan(string $action, User $user, Projet $projet): bool
    {
        // Empêche tous les accès aux projets des autres sociétés
        if (!$this->societeChecker->isSameSociete($user, $projet)) {
            return false;
        }

        // L'admin a tous les droits sur tous les projets
        if ($this->authChecker->isGranted('ROLE_FO_ADMIN')) {
            return true;
        }

        // Le chef de projet a tous les droits sur son propre projet
        if ($this->participantService->hasRoleOnProjet($user, $projet, Role::CDP)) {
            return true;
        }

        switch ($action) {
            case 'view':
                // L'utilisateur peut voir le projet s'il a un rôle dessus
                return $this->participantService->isParticipant($user, $projet);

            case 'edit':
            case 'delete':
                // L'utilisateur ne peut pas modifier les infos du projet, ni supprimer le projet
                return false;
        }
    }
}
