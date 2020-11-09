<?php

namespace App\Security\Voter;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\User;
use App\Exception\RdiException;
use App\Role;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * peut effectuer une action sur un projet donné.
 */
class FaitMarquantVoter extends Voter
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
        return $subject instanceof FaitMarquant && in_array($attribute, [
            'view',
            'edit',
            'delete',
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $attribute
     * @param FaitMarquant|Projet $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->userCanDo($token->getUser(), $attribute, $subject);
    }

    private function userCanDo(User $user, string $action, FaitMarquant $faitMarquant): bool
    {
        $projet = $faitMarquant->getProjet();
        $userRole = $this->participantService->getRoleOfUserOnProjet($user, $projet);

        // L'admin de la société peut modifier tous les faits marquants des projets de sa propre société
        if (
            $this->authChecker->isGranted('ROLE_FO_ADMIN')
            && $this->societeChecker->isSameSociete($projet, $user)
        ) {
            return true;
        }

        // Le chef de projet peut tout faire sur les faits marquants de son propre projet
        if ($this->participantService->hasRole($userRole, Role::CDP)) {
            return true;
        }

        // Le contributeur qui a créé le fait marquant peut l'éditer
        if (
            $this->participantService->hasRole($userRole, Role::CONTRIBUTEUR)
            && $faitMarquant->getCreatedBy() === $user
        ) {
            return true;
        }

        // L'observateur peut voir les faits marquants
        if (
            $this->participantService->hasRole($userRole, Role::OBSERVATEUR)
            && $action === 'view'
        ) {
            return true;
        }

        // Sinon on refuse l'action.
        return false;
    }
}
