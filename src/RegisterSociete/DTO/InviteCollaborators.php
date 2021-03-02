<?php

namespace App\RegisterSociete\DTO;

class InviteCollaborators
{
    private ?string $email0;

    private string $role0;

    private ?string $email1;

    private string $role1;

    public function __construct()
    {
        $this->email0 = null;
        $this->role0 = 'ROLE_FO_USER';
        $this->email1 = null;
        $this->role1 = 'ROLE_FO_USER';
    }

    public function getEmail0(): ?string
    {
        return $this->email0;
    }

    public function setEmail0(?string $email0): self
    {
        $this->email0 = $email0;

        return $this;
    }

    public function getRole0(): string
    {
        return $this->role0;
    }

    public function setRole0(string $role0): self
    {
        $this->role0 = $role0;

        return $this;
    }

    public function getEmail1(): ?string
    {
        return $this->email1;
    }

    public function setEmail1(?string $email1): self
    {
        $this->email1 = $email1;

        return $this;
    }

    public function getRole1(): string
    {
        return $this->role1;
    }

    public function setRole1(string $role1): self
    {
        $this->role1 = $role1;

        return $this;
    }
}