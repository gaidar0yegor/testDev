<?php

namespace App\Onboarding;

use App\Onboarding\Notification\FinalizeInscription;
use App\Onboarding\Step\FinalizeInscriptionStep;
use App\Repository\SocieteUserRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Classe qui s'occupe d'envoyer des notifications automatique
 * pour relancer les utilisateurs qui sont encore dans l'onboarding.
 */
class ReminderNotification
{
    private SocieteUserRepository $societeUserRepository;

    private Onboarding $onboarding;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        SocieteUserRepository $societeUserRepository,
        Onboarding $onboarding,
        EventDispatcherInterface $dispatcher
    ) {
        $this->societeUserRepository = $societeUserRepository;
        $this->onboarding = $onboarding;
        $this->dispatcher = $dispatcher;
    }

    public function dispatchReminderNotifications(): void
    {
        $this->dispatchFinalizeInscriptionNotification();
    }

    public function dispatchFinalizeInscriptionNotification(): void
    {
        $societeUsers = $this
            ->societeUserRepository
            ->findAllNotifiableUsersNotYetJoined('notificationOnboardingEnabled')
        ;

        foreach ($societeUsers as $societeUser) {
            $step = $this->onboarding->getStepFor($societeUser, FinalizeInscriptionStep::class);

            if (!$step['completed']) {
                $this->dispatcher->dispatch(new FinalizeInscription($societeUser));
            }
        }
    }
}
