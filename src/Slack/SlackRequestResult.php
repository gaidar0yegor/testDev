<?php

namespace App\Slack;

use DateTime;
use DateTimeInterface;

class SlackRequestResult
{
    private bool $success;

    private string $message;

    private DateTimeInterface $sentAt;

    public function __construct(bool $success, string $message, ?DateTimeInterface $sentAt = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->sentAt = $sentAt ?? new DateTime();
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getSentAt(): DateTimeInterface
    {
        return $this->sentAt;
    }
}
