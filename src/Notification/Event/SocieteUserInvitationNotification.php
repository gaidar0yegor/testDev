<?php

namespace App\Notification\Event;

use App\Entity\SocieteUser;
use App\Entity\User;

/**
 * Event émit lorsqu'il faut envoyer une invitation
 * à un user pas encore inscrit, sur une société.
 *
 * Exemple : Un admin invite un nouvel user dans sa société.
 */
class SocieteUserInvitationNotification
{
    /**
     * User à inviter sur la société
     */
    private SocieteUser $societeUser;

    /**
     * User qui envoie la notification
     */
    private User $from;

    public function __construct(SocieteUser $societeUser, User $from)
    {
        $this->societeUser = $societeUser;
        $this->from = $from;
    }

    public function getSocieteUser(): SocieteUser
    {
        return $this->societeUser;
    }

    public function getFrom(): User
    {
        return $this->from;
    }
}
