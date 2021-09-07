<?php

namespace App\Onboarding;

use App\Onboarding\Notification\AddProjects;
use App\Onboarding\Notification\FinalizeInscription;
use App\Onboarding\Notification\InviteCollaborators;
use App\Onboarding\Step\AddProjetStep;
use App\Onboarding\Step\InviteUserStep;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleSociete;
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
        $this->dispatchOnboardingStepNotification();
    }

    public function dispatchFinalizeInscriptionNotification(): void
    {
        $societeUsers = $this
            ->societeUserRepository
            ->findAllNotifiableUsersNotYetJoined('notificationOnboardingEnabled')
        ;

        foreach ($societeUsers as $societeUser) {
            $this->dispatcher->dispatch(new FinalizeInscription($societeUser));
        }
    }

    public function dispatchOnboardingStepNotification(): void
    {
        $societeUsers = $this
            ->societeUserRepository
            ->findAllNotifiableUsers('notificationEnabled')
        ;

        foreach ($societeUsers as $societeUser) {
            if (RoleSociete::ADMIN === $societeUser->getRole()) {
                $step = $this->onboarding->getStepFor($societeUser, InviteUserStep::class);

                if (!$step['completed']) {
                    $this->dispatcher->dispatch(new InviteCollaborators($societeUser));
                    continue;
                }

                $step = $this->onboarding->getStepFor($societeUser, AddProjetStep::class);

                if (!$step['completed']) {
                    $this->dispatcher->dispatch(new AddProjects($societeUser));
                    continue;
                }
            }

            if (in_array($societeUser->getRole(), [RoleSociete::ADMIN, RoleSociete::CDP], true)) {
                $step = $this->onboarding->getStepFor($societeUser, AddProjetStep::class);

                if (!$step['completed']) {
                    $this->dispatcher->dispatch(new AddProjects($societeUser));
                    continue;
                }
            }
        }
    }
}
