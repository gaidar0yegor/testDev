<?php

namespace App\DTO;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Classe utilisée pour stocker les filtres
 * lors d'une génération de feuilles de temps.
 */
class FilterTimesheet
{
    private array $users;

    private ?DateTime $from;

    private ?DateTime $to;

    /**
     * "pdf" or "html"
     */
    private string $format;

    public function __construct()
    {
        $this->users = [];
        $this->from = new DateTime();
        $this->to = new DateTime();
        $this->format = 'pdf';
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(array $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getFrom(): DateTime
    {
        return $this->from;
    }

    public function setFrom(?DateTime $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): DateTime
    {
        return $this->to;
    }

    public function setTo(?DateTime $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->to < $this->from) {
            $context
                ->buildViolation('La date de fin doit être égale ou après la date de début.')
                ->atPath('to')
                ->addViolation()
            ;
        }
    }
}
