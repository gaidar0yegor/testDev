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
class CreateFaitMarquantVoter extends Voter
{
    private $participantService;

    private $societeChecker;

    public function __construct(
        ParticipantService $participantService,
        SocieteChecker $societeChecker
    ) {
        $this->participantService = $participantService;
        $this->societeChecker = $societeChecker;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Projet && $attribute === 'create_fait_marquant';
    }

    /**
     * {@inheritDoc}
     *
     * @param string $attribute
     * @param Projet $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->userCanCreateFaitOnProjet($token->getUser(), $subject);
    }

    private function userCanCreateFaitOnProjet(User $user, Projet $projet): bool
    {
        // User ne peut pas créer de fait marquant sur un projet d'une société autre que la sienne
        if (!$this->societeChecker->isSameSociete($projet, $user)) {
            return false;
        }

        // User doit être au moins contributeur sur ce projet
        if (!$this->participantService->hasRoleOnProjet($user, $projet, Role::CONTRIBUTEUR)) {
            return false;
        }

        return true;
    }
}
