<?php

namespace App\ObservateurExterne\Notification;

use App\Entity\ProjetObservateurExterne;

class InvitationObservateurExterneNotification
{
    private ProjetObservateurExterne $projetObservateurExterne;

    public function __construct(ProjetObservateurExterne $projetObservateurExterne)
    {
        $this->projetObservateurExterne = $projetObservateurExterne;
    }

    public function getProjetObservateurExterne(): ProjetObservateurExterne
    {
        return $this->projetObservateurExterne;
    }
}
