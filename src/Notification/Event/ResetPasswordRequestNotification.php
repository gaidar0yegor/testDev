<?php

namespace App\Notification\Event;

use App\Entity\User;

/**
 * Event émit lorsqu'un utilisateur a oublié son mot de passe
 * et qu'il demande un lien de réinitialisation de mot de passe.
 */
class ResetPasswordRequestNotification
{
    /**
     * User qui souhaite réinitialiser son mot de de passe
     */
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
