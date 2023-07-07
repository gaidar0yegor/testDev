<?php

namespace App\Security\Voter;

use App\Security\Role\RoleLabo;
use App\MultiSociete\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * a bien le rôle requis dans le laboratoire actuel.
 */
class RoleLaboVoter extends Voter
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
        return RoleLabo::isValidRole($attribute);
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (!$this->userContext->hasUser()) {
            return false;
        }

        $userRole = $this->userContext->getUserBook()->getRole();

        return RoleLabo::hasRole($userRole, $attribute);
    }
}
