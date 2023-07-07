<?php

namespace App\Notification\Event;

/**
 * Quand cet event est émit,
 * c'est qu'il faut envoyer un rappel aux utilisateurs
 * de la société pour qu'ils créent leurs éventuels faits marquants.
 */
class RappelCreationFaitMarquantsNotification extends SocieteNotification
{
}
