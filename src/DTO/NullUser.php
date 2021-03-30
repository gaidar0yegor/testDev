<?php

namespace App\DTO;

use App\Entity\SocieteUser;
use App\Entity\User;

/**
 * NullObject for User, returned by SocieteUser when User is invited but not joined yet.
 * Useful for templates, to not add lot of "societeUser.user is null" checks,
 * or "societeUser.user.email|default(societeUser.invitationEmail)" everywhere.
 */
class NullUser extends User
{
    private SocieteUser $societeUser;

    public function __construct(SocieteUser $societeUser)
    {
        $this->societeUser = $societeUser;
    }

    public function getFullname(): string
    {
        return '-';
    }

    public function getShortname(): string
    {
        return '-';
    }

    public function getFullnameOrEmail(): string
    {
        return $this->societeUser->getInvitationEmail();
    }

    public function getEmail(): ?string
    {
        return $this->societeUser->getInvitationEmail();
    }
}
