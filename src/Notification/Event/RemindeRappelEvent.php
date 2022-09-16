<?php

namespace App\Notification\Event;

use App\Entity\Rappel;

/**
 * Event émit pour notifier un rappel
 */
class RemindeRappelEvent
{
    private Rappel $rappel;

    public function __construct(
        Rappel $rappel
    ) {
        $this->rappel = $rappel;
    }

    public function getRappel(): Rappel
    {
        return $this->rappel;
    }
}
