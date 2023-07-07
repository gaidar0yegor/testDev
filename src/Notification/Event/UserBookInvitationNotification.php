<?php

namespace App\Notification\Event;

use App\Entity\LabApp\UserBookInvite;
use App\Entity\User;

/**
 * Event émit lorsqu'il faut envoyer une invitation
 * à un user pas encore inscrit sur un laboratoire.
 *
 * Exemple : Un admin invite un nouveau cahier de laboratoire dans son laboratoire.
 */
class UserBookInvitationNotification
{
    /**
     * UserBookInvite à inviter sur le laboratoire
     */
    private UserBookInvite $userBookInvite;

    /**
     * User qui envoie la notification
     */
    private User $from;

    public function __construct(UserBookInvite $userBookInvite, User $from)
    {
        $this->userBookInvite = $userBookInvite;
        $this->from = $from;
    }

    public function getUserBookInvite(): UserBookInvite
    {
        return $this->userBookInvite;
    }

    public function getFrom(): User
    {
        return $this->from;
    }
}
