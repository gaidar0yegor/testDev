<?php

namespace App\MultiSociete;

use App\Entity\SocieteUser;
use App\Entity\User;
use App\MultiSociete\Exception\NoCurrentSocieteException;
use App\Security\Exception\UnexpectedUserException;
use App\Security\Exception\NoLoggedInUserException;
use Symfony\Component\Security\Core\Security;

/**
 * Permet de récupérer l'user actuellement connecter,
 * et sur quelle société l'user a actuellement switché.
 */
class UserContext
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function hasUser(): bool
    {
        return null !== $this->security->getUser();
    }

    /**
     * Get current logged in user.
     * This user can have an access to multiple societe through relation SocieteUser.
     *
     * @throws NoLoggedInUserException If this method is called when no user is logged in.
     *                                 Use UserContext::hasUser() if this case can happen.
     * @throws UnexpectedUserException If an user is logged in, but not an instance of User, maybe wrong firewall.
     */
    public function getUser(): User
    {
        $user = $this->security->getUser();

        if (null === $user) {
            throw new NoLoggedInUserException(
                'Cannot retrieve User from UserContext->getUser() because there is no logged in user.'
            );
        }

        if (!$user instanceof User) {
            throw new UnexpectedUserException($user);
        }

        return $user;
    }

    /**
     * Returns whether there is a logged in user and this user has switched to a societe.
     */
    public function hasSocieteUser(): bool
    {
        return $this->hasUser() && null !== $this->getUser()->getCurrentSocieteUser();
    }

    /**
     * Returns current SocieteUser.
     * The user is the one logged in, and the societe is the one the user switched to.
     *
     * @throws NoLoggedInUserException If this method is called when no user is logged in.
     *                                 Use UserContext::hasUser() if this case can happen.
     * @throws UnexpectedUserException If an user is logged in, but not an instance of User, maybe wrong firewall.
     * @throws NoCurrentSocieteException When user logged in but not switched to any societe yet.
     */
    public function getSocieteUser(): SocieteUser
    {
        if ($this->hasUser() && null === $this->getUser()->getCurrentSocieteUser()) {
            throw new NoCurrentSocieteException();
        }

        return $this->getUser()->getCurrentSocieteUser();
    }

    /**
     * Switch societe by setting the current SocieteUser.
     * You need to flush enitty manager.
     */
    public function switchSociete(SocieteUser $societeUser): void
    {
        $this->getUser()->setCurrentSocieteUser($societeUser);
    }

    /**
     * Switch societe by setting the current SocieteUser.
     * You need to flush enitty manager.
     */
    public function disconnectSociete(): void
    {
        $this->getUser()->setCurrentSocieteUser(null);
    }
}
