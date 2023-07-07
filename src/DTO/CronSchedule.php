<?php

namespace App\DTO;

class CronSchedule
{
    public ?int $dayOfMonth = null;

    public ?int $dayOfWeek = null;

    public ?int $hour = null;

    public ?int $minute = null;

    public static function createFromString(string $scheduleString): self
    {
        $tokens = explode(' ', $scheduleString);

        $cronSchedule = new self();

        $cronSchedule->dayOfMonth = '*' === $tokens[2] ? null : intval($tokens[2]);
        $cronSchedule->dayOfWeek = '*' === $tokens[4] ? null : intval($tokens[4]);
        $cronSchedule->hour = '*' === $tokens[1] ? null : intval($tokens[1]);
        $cronSchedule->minute = '*' === $tokens[0] ? null : intval($tokens[0]);

        return $cronSchedule;
    }

    public static function everyDay(int $hour, int $minute): self
    {
        $cronSchedule = new self();

        $cronSchedule->hour = $hour;
        $cronSchedule->minute = $minute;

        return $cronSchedule;
    }

    public static function everyWeek(int $dayOfWeek, int $hour, int $minute): self
    {
        $cronSchedule = new self();

        $cronSchedule->dayOfWeek = $dayOfWeek;
        $cronSchedule->hour = $hour;
        $cronSchedule->minute = $minute;

        return $cronSchedule;
    }

    public static function everyMonth(int $dayOfMonth, int $hour, int $minute): self
    {
        $cronSchedule = new self();

        $cronSchedule->dayOfMonth = $dayOfMonth;
        $cronSchedule->hour = $hour;
        $cronSchedule->minute = $minute;

        return $cronSchedule;
    }

    public function __toString()
    {
        return join(' ', [
            null === $this->minute ? '*' : $this->minute,
            null === $this->hour ? '*' : $this->hour,
            null === $this->dayOfMonth ? '*' : $this->dayOfMonth,
            '*',
            null === $this->dayOfWeek ? '*' : $this->dayOfWeek,
        ]);
    }
}
