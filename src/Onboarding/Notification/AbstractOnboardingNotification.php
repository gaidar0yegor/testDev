<?php

namespace App\Onboarding\Notification;

use App\Entity\SocieteUser;

abstract class AbstractOnboardingNotification
{
    private SocieteUser $societeUser;

    public function __construct(SocieteUser $societeUser)
    {
        $this->societeUser = $societeUser;
    }

    public function getSocieteUser(): SocieteUser
    {
        return $this->societeUser;
    }
}
