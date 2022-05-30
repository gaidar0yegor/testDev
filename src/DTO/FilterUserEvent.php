<?php

namespace App\DTO;

/**
 * Classe utilisée pour stocker les filtres
 * lors d'une génération d'une calendrier des évènements des utilisateurs
 */
class FilterUserEvent
{
    private array $users;

    private array $eventTypes;

    public function __construct()
    {
        $this->users = [];
        $this->eventTypes = [];
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

    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }

    public function setEventTypes(array $eventTypes): self
    {
        $this->eventTypes = $eventTypes;

        return $this;
    }

}
