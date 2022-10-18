<?php

namespace App\DTO;

use App\Entity\Evenement;

/**
 * Classe utilisée pour stocker les filtres
 * lors d'une génération d'une calendrier des évènements des utilisateurs
 */
class FilterUserEvenement
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
        $eventTypes = $this->eventTypes;

        if (in_array(Evenement::TYPE_PERSONAL, $eventTypes, true)){
            unset($eventTypes[array_search(Evenement::TYPE_PERSONAL, $eventTypes)]);
        }

        return $eventTypes;
    }

    public function setEventTypes(array $eventTypes): self
    {
        $this->eventTypes = $eventTypes;

        return $this;
    }

}
