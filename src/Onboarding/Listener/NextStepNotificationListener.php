<?php

namespace App\Onboarding\Listener;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Entity\User;
use App\Exception\RdiException;
use App\Onboarding\ReminderNotification;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Envoi une notification d'onboarding lorsqu'une étape vient d'être validée.
 */
class NextStepNotificationListener
{
    private ReminderNotification $reminderNotification;

    public function __construct(ReminderNotification $reminderNotification)
    {
        $this->reminderNotification = $reminderNotification;
    }

    public function societeUserUpdating(SocieteUser $societeUser, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($societeUser);

        if (!isset($changes['user'])) {
            return;
        }

        $userChange = $changes['user'];

        if (null === $userChange[0] && $userChange[1] instanceof User) {
            $this->reminderNotification->dispatchOnboardingStepNotificationTo($societeUser, true);
        }
    }

    public function projetPersisted(Projet $projet, LifecycleEventArgs $args): void
    {
        try {
            $this->reminderNotification->dispatchOnboardingStepNotificationTo($projet->getChefDeProjet(), true);
        } catch (RdiException $e) {
            return;
        }
    }
}
