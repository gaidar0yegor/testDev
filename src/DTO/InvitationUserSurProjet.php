<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe utilisée pour le modèle du form InviteUserSurProjetType
 * qui sert à inviter un nouvel utilisateur sur un projet donné
 * à partir d'un email.
 */
class InvitationUserSurProjet
{
    /**
     * @Assert\Email(mode="strict")
     */
    private string $email;

    private string $role;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }
}
