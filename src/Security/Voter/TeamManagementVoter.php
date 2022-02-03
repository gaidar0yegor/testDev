<?php

namespace App\Security\Voter;

use App\Service\EquipeChecker;
use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un UserSociete a le droit d'accèder à les fontionnalités d'une équipe
 * (Administrateur ou chef d'équipe)
 */
class TeamManagementVoter extends Voter
{
    public const NAME = 'team_management';

    private EquipeChecker $equipeChecker;

    private UserContext $userContext;

    public function __construct(EquipeChecker $equipeChecker, UserContext $userContext)
    {
        $this->equipeChecker = $equipeChecker;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return self::NAME === $attribute;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->equipeChecker->hasPermission($subject);
    }
}
