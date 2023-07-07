<?php

namespace App\Security\Voter;

use App\Security\Role\RoleSociete;
use App\MultiSociete\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * a bien le rôle requis dans la société actuelle.
 */
class RoleSocieteVoter extends Voter
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return RoleSociete::isValidRole($attribute);
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (!$this->userContext->hasUser()) {
            return false;
        }

        $userRole = $this->userContext->getSocieteUser()->getRole();

        return RoleSociete::hasRole($userRole, $attribute);
    }
}
