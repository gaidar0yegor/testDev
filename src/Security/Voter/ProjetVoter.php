<?php

namespace App\Security\Voter;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Security\Role\RoleProjet;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use App\Security\Role\RoleSociete;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * peut effectuer une action sur un projet donné.
 */
class ProjetVoter extends Voter
{
    private $participantService;

    private UserContext $userContext;

    private $societeChecker;

    public function __construct(
        ParticipantService $participantService,
        UserContext $userContext,
        SocieteChecker $societeChecker
    ) {
        $this->participantService = $participantService;
        $this->societeChecker = $societeChecker;
        $this->userContext = $userContext;
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
        return $this->societeUserCan($attribute, $this->userContext->getSocieteUser(), $subject);
    }

    private function societeUserCan(string $action, SocieteUser $societeUser, Projet $projet): bool
    {
        // Empêche tous les accès aux projets des autres sociétés
        if (!$this->societeChecker->isSameSociete($societeUser, $projet)) {
            return false;
        }

        // L'admin a tous les droits sur tous les projets
        if (RoleSociete::hasRole($societeUser->getRole(), RoleSociete::ADMIN)) {
            return true;
        }

        // Le chef de projet a tous les droits sur son propre projet
        if ($this->participantService->hasRoleOnProjet($societeUser, $projet, RoleProjet::CDP)) {
            return true;
        }

        switch ($action) {
            case 'view':
                // L'utilisateur peut voir le projet s'il a un rôle dessus
                return $this->participantService->isParticipant($societeUser, $projet);

            case 'edit':
            case 'delete':
                // L'utilisateur ne peut pas modifier les infos du projet, ni supprimer le projet
                return false;
        }
    }
}
