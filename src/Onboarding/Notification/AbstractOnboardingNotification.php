<?php

namespace App\Onboarding\Notification;

use App\Entity\SocieteUser;

/**
 * Lorqu'une notification de ce type est émise,
 * il faut envoyer une relance automatique
 * si le délai d'attente entre 2 relances est suffisant.
 */
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
