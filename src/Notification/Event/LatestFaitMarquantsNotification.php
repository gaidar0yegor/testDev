<?php

namespace App\Notification\Event;

/**
 * Quand cet event est émit,
 * c'est qu'il faut envoyer la liste des derniers faits marquants
 * aux utilisateurs de la société.
 */
class LatestFaitMarquantsNotification extends SocieteNotification
{
}
