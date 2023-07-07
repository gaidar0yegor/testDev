<?php

namespace App\DTO;

use App\Entity\SocieteUser;
use App\Entity\User;
use App\UserResourceInterface;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * NullObject for User, returned by SocieteUser when User is invited but not joined yet.
 * Useful for templates, to not add lot of "societeUser.user is null" checks,
 * or "societeUser.user.email|default(societeUser.invitationEmail)" everywhere.
 */
class NullUser extends User
{
    private UserResourceInterface $userResource;

    public function __construct(UserResourceInterface $userResource)
    {
        $this->userResource = $userResource;
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
        if (null !== $this->userResource->getInvitationEmail()) {
            return $this->userResource->getInvitationEmail();
        }

        if (null !== $this->userResource->getInvitationTelephone()) {
            return PhoneNumberUtil::getInstance()->format(
                $this->userResource->getInvitationTelephone(),
                PhoneNumberFormat::NATIONAL
            );
        }

        return '-';
    }

    public function getEmail(): ?string
    {
        return $this->userResource->getInvitationEmail();
    }

    public function getTelephone(): ?PhoneNumber
    {
        return $this->userResource->getInvitationTelephone();
    }
}
