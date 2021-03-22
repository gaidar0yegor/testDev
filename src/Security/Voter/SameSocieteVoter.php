<?php

namespace App\Security\Voter;

use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'une entité
 * est dans la même société que l'user actuellement connecté.
 */
class SameSocieteVoter extends Voter
{
    public const NAME = 'same_societe';

    private SocieteChecker $societeChecker;

    private UserContext $userContext;

    public function __construct(SocieteChecker $societeChecker, UserContext $userContext)
    {
        $this->societeChecker = $societeChecker;
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
        $societeUser = $this->userContext->getSocieteUser();

        return $this->societeChecker->isSameSociete($subject, $societeUser);
    }
}
