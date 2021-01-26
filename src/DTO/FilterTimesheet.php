<?php

namespace App\DTO;

use App\Validator as AppAssert;
use DateTime;

/**
 * Classe utilisée pour stocker les filtres
 * lors d'une génération de feuilles de temps.
 *
 * @AppAssert\DatesOrdered(
 *      start="from",
 *      end="to"
 * )
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
}
