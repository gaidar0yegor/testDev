<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class InitSociete
{
    private string $raisonSociale;

    private ?string $siret;

    /**
     * @Assert\Email(mode="strict")
     */
    private string $adminEmail;

    public function getRaisonSociale(): string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): self
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    public function setAdminEmail(string $adminEmail): self
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }
}
