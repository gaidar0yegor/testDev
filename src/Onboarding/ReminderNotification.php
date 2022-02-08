<?php

namespace App\Onboarding;

use App\Entity\SocieteUser;
use App\Onboarding\Notification\AddProjects;
use App\Onboarding\Notification\FinalizeInscription;
use App\Onboarding\Notification\FinalSuccess;
use App\Onboarding\Notification\InviteCollaborators;
use App\Onboarding\Step\AddProjetStep;
use App\Onboarding\Step\InviteUserStep;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleSociete;
use DateTime;
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
            if (!$this->shouldResendNotification($societeUser)) {
                continue;
            }

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
            if (!$this->shouldResendNotification($societeUser)) {
                continue;
            }

            $this->dispatchOnboardingStepNotificationTo($societeUser);
        }
    }

    /**
     * Dispatch an onboarding notification to a specific user
     * to make him receive the next step onboarding message.
     *
     * @param SocieteUser $societeUser The specific user to send to
     * @param bool $forceFinalNotification If set to true, also send the final "bravo" notification.
     *                                     Should not be used on automatic notifications,
     *                                     only once previous step is done.
     */
    public function dispatchOnboardingStepNotificationTo(
        SocieteUser $societeUser,
        bool $forceFinalNotification = false
    ): void {
        if (RoleSociete::ADMIN === $societeUser->getRole()) {
            $step = $this->onboarding->getStepFor($societeUser, InviteUserStep::class);

            if (!$step['completed']) {
                $this->dispatcher->dispatch(new InviteCollaborators($societeUser));
                return;
            }

            $step = $this->onboarding->getStepFor($societeUser, AddProjetStep::class);

            if (!$step['completed']) {
                $this->dispatcher->dispatch(new AddProjects($societeUser));
                return;
            }
        }

        if (in_array($societeUser->getRole(), [RoleSociete::ADMIN, RoleSociete::CDP], true)) {
            $step = $this->onboarding->getStepFor($societeUser, AddProjetStep::class);

            if (!$step['completed']) {
                $this->dispatcher->dispatch(new AddProjects($societeUser));
                return;
            }
        }

        if (!$forceFinalNotification || $societeUser->getNotificationOnboardingFinished()) {
            return;
        }

        $this->dispatcher->dispatch(new FinalSuccess($societeUser));
    }

    private function shouldResendNotification(SocieteUser $societeUser): bool
    {
        if ($societeUser->getNotificationOnboardingFinished()) {
            return false;
        }

        if (null !== $societeUser->getNotificationOnboardingLastSentAt()) {
            $timeThreshold = (new DateTime())
                ->modify('-' . $societeUser->getSociete()->getOnboardingNotificationEvery())
                ->modify('+2 hours')
            ;

            if ($societeUser->getNotificationOnboardingLastSentAt() > $timeThreshold) {
                return false;
            }
        }

        return true;
    }
}
