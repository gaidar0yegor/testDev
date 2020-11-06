<?php

namespace App\Security;

use App\Exception\RdiException;
use App\HasSocieteInterface;
use App\Service\SocieteChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'une entité
 * est dans la même société que l'user actuellement connecté.
 */
class SameSocieteVoter extends Voter
{
    private $societeChecker;

    public function __construct(SocieteChecker $societeChecker)
    {
        $this->societeChecker = $societeChecker;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return 'same_societe' === $attribute;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof HasSocieteInterface) {
            throw new RdiException('SameSocieteVoter expected to be used on a HasSocieteInterface');
        }

        return $this->societeChecker->isSameSociete($subject, $user);
    }
}
