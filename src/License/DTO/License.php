<?php

namespace App\License\DTO;

use App\Entity\Societe;
use App\HasSocieteInterface;
use DateTime;

class License implements HasSocieteInterface
{
    private string $name;

    private ?string $description;

    private Societe $societe;

    private DateTime $expirationDate;

    private array $quotas;

    public function __construct()
    {
        $this->description = null;
        $this->quotas = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(Societe $societe)
    {
        $this->societe = $societe;

        return $this;
    }

    public function getExpirationDate(): DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getQuotas(): array
    {
        return $this->quotas;
    }

    public function setQuotas(array $quotas): self
    {
        $this->quotas = $quotas;

        return $this;
    }
}
