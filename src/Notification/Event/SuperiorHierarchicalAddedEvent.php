<?php

namespace App\Notification\Event;

use App\Entity\SocieteUser;

/**
 * Event émit lorsqu'un utilisateur désigne son supérieur hiérarchique
 */
class SuperiorHierarchicalAddedEvent
{
    /**
     * SocieteUser N
     */
    private SocieteUser $societeUser;

    public function __construct(SocieteUser $societeUser)
    {
        $this->societeUser = $societeUser;
    }

    public function getSocieteUser(): SocieteUser
    {
        return $this->societeUser;
    }

    public function hasSuperior(): bool
    {
        return $this->getSocieteUser()->getMySuperior() instanceof SocieteUser;
    }
}
