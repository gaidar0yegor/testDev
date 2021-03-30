<?php

namespace App\Service\Timesheet\Event;

use App\Entity\SocieteUser;

class TimesheetEvent
{
    public const GENERATED = 'app.event.timesheet.generated';

    /**
     * @var SocieteUser The societeUser who generated timesheets
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
}
