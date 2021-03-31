<?php

namespace App;

use App\Entity\User;

/**
 * Représente une ressource appartenant à un User.
 */
interface UserResourceInterface
{
    public function getUser(): User;
}
