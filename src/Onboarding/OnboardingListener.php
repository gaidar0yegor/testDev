<?php

namespace App\Onboarding;

use App\Entity\SocieteUser;
use App\Entity\User;
use App\MultiSociete\UserContext;
use App\Security\Role\RoleSociete;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class OnboardingListener implements EventSubscriberInterface
{
    private Onboarding $onboarding;

    private SessionInterface $session;

    private UserContext $userContext;

    private EntityManagerInterface $em;

    public function __construct(
        Onboarding $onboarding,
        SessionInterface $session,
        UserContext $userContext,
        EntityManagerInterface $em
    ) {
        $this->onboarding = $onboarding;
        $this->session = $session;
        $this->userContext = $userContext;
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'addOnboardingMessage',
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }

    public function addOnboardingMessage(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->userContext->hasUser() || !$this->userContext->hasSocieteUser()) {
            return;
        }

        $societeUser = $this->userContext->getSocieteUser();

        if (!$this->shouldDisplayOnboarding($societeUser)) {
            return;
        }

        $steps = $this->onboarding->getStepsFor($societeUser);

        $this->session->set('onboardingSteps', $steps);

        if ($this->onboarding->allCompleted($steps)) {
            $societeUser->getUser()->setOnboardingEnabled(false);
            $this->em->flush();
        }
    }

    /**
     * Re-enables onboarding for user on login
     * if he still has important steps to complete.
     */
    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        if (!$this->userContext->hasSocieteUser()) {
            return;
        }

        // Onboarding already enabled, do nothing
        if ($user->getOnboardingEnabled()) {
            return;
        }

        $societeUser = $this->userContext->getSocieteUser();
        $steps = $this->onboarding->getStepsFor($societeUser);

        if (!RoleSociete::hasRole($societeUser->getRole(), RoleSociete::CDP)) {
            return;
        }

        if (!$this->onboarding->allImportantCompleted($steps)) {
            $user->setOnboardingEnabled(true);
            $this->em->flush();
        }
    }

    private function shouldDisplayOnboarding(SocieteUser $societeUser): bool
    {
        // Only display to admins and chefs de projet
        if (!RoleSociete::hasRole($societeUser->getRole(), RoleSociete::CDP)) {
            return false;
        }

        // Don't display if onboarding disabled for this user
        if (!$societeUser->getUser()->getOnboardingEnabled()) {
            return false;
        }

        return true;
    }
}
