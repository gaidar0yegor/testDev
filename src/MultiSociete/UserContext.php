<?php

namespace App\MultiSociete;

use App\Entity\LabApp\UserBook;
use App\Entity\SocieteUser;
use App\Entity\User;
use App\License\LicenseService;
use App\MultiPlateform\Exception\CurrentUserContextAccessDeniedException;
use App\MultiSociete\Exception\NoCurrentSocieteException;
use App\MultiUserBook\Exception\NoCurrentUserBookException;
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
    private LicenseService $licenseService;

    public function __construct(Security $security, LicenseService $licenseService)
    {
        $this->security = $security;
        $this->licenseService = $licenseService;
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
     * Returns whether there is a logged in user and this user has switched to a userbook.
     */
    public function hasUserBook(): bool
    {
        return $this->hasUser() && null !== $this->getUser()->getCurrentUserBook();
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
     * Returns current UserBook.
     * The user is the one logged in, and the UserBook is the one the user switched to.
     *
     * @throws NoCurrentUserBookException When user logged in but not switched to any UserBook yet.
     */
    public function getUserBook(): UserBook
    {
        if ($this->hasUser() && null === $this->getUser()->getCurrentUserBook()) {
            throw new NoCurrentUserBookException();
        }

        return $this->getUser()->getCurrentUserBook();
    }

    /**
     * Switch societe by setting the current SocieteUser.
     * You need to flush enitty manager.
     */
    public function switchSociete(SocieteUser $societeUser): void
    {
        if (!$societeUser->hasUser()) {
            throw new CurrentUserContextAccessDeniedException(
                'Cannot switch to this access because user is null'
            );
        }

        $user = $societeUser->getUser();

        if ($user !== $this->getUser()) {
            throw new CurrentUserContextAccessDeniedException(
                'Cannot switch to this access because not the same user as logged in'
            );
        }

        if (!$societeUser->getEnabled()) {
            throw new CurrentUserContextAccessDeniedException(
                'Cannot switch to this access because disabled'
            );
        }

        $this->getUser()->setCurrentSocieteUser($societeUser);
    }

    /**
     * You need to flush enitty manager.
     */
    public function switchUserBook(UserBook $userBook): void
    {
        if (!$userBook->hasUser()) {
            throw new CurrentUserContextAccessDeniedException(
                'Cannot switch to this access because user is null'
            );
        }

        $user = $userBook->getUser();

        if ($user !== $this->getUser()) {
            throw new CurrentUserContextAccessDeniedException(
                'Cannot switch to this access because not the same user as logged in'
            );
        }

        $this->getUser()->setCurrentUserBook($userBook);
    }

    /**
     * Check if societe has a try license
     */
    public function hasTryLicense(): bool
    {
        if (!$this->hasSocieteUser()){
            throw new NoLoggedInUserException(
                'Cannot retrieve your access to a Societe.'
            );
        }

        return $this->licenseService->checkHasTryLicense($this->getSocieteUser()->getSociete());
    }

    /**
     * Switch societe by setting the current SocieteUser.
     * You need to flush enitty manager.
     */
    public function disconnectSociete(): void
    {
        $this->getUser()->setCurrentSocieteUser(null);
    }

    public function disconnectUserLabo(): void
    {
        $this->getUser()->setCurrentUserBook(null);
    }
}
