<?php

namespace App\DTO;

class RecommandationMessage
{
    /**
     * Mail from.
     * Either the currently logged in user email,
     * or the default application mail from
     */
    private string $from;

    /**
     * Email on which to send the message.
     */
    private string $to;

    private string $subject = 'Je vous recommande RDI-Manager';

    private ?string $customText = null;

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getCustomText(): ?string
    {
        return $this->customText;
    }

    public function setCustomText(?string $customText): self
    {
        $this->customText = $customText;

        return $this;
    }
}
