<?php

namespace App\Service\Timesheet\Event;

use App\Entity\User;

class TimesheetEvent
{
    public const GENERATED = 'app.event.timesheet.generated';

    /**
     * @var User The user who generated timesheets
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
