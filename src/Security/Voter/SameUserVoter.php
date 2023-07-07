<?php

namespace App\Security\Voter;

use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use App\UserResourceInterface;
use Doctrine\Common\Util\ClassUtils;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'une entité
 * appartient bien à l'User actuellement connecté.
 */
class SameUserVoter extends Voter
{
    public const NAME = 'same_user';

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
        if (!$subject instanceof UserResourceInterface) {
            throw new InvalidArgumentException(sprintf(
                '%s expects an instance of %s as argument, got "%s".',
                self::class,
                UserResourceInterface::class,
                ClassUtils::getClass($subject)
            ));
        }

        return $subject->getUser() === $token->getUser();
    }
}
