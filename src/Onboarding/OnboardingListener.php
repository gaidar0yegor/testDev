<?php

namespace App\Onboarding;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OnboardingListener implements EventSubscriberInterface
{
    private Onboarding $onboarding;

    private SessionInterface $session;

    private TokenStorageInterface $tokenStorage;

    private AuthorizationCheckerInterface $authChecker;

    private EntityManagerInterface $em;

    public function __construct(
        Onboarding $onboarding,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        EntityManagerInterface $em
    ) {
        $this->onboarding = $onboarding;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'addOnboardingMessage',
        ];
    }

    public function addOnboardingMessage(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $user = $this->getUser();

        if (null === $user || !$this->shouldDisplayOnboarding($user)) {
            return;
        }

        $steps = $this->onboarding->getStepsFor($user);

        $this->session->set('onboardingSteps', $steps);

        if ($this->onboarding->allCompleted($steps)) {
            $user->setOnboardingEnabled(false);
            $this->em->flush();
        }
    }

    private function shouldDisplayOnboarding(User $user): bool
    {
        // Only display to admins
        if (!$this->authChecker->isGranted('ROLE_FO_ADMIN', $user)) {
            return false;
        }

        // Don't display if onboarding disabled for this user
        if (!$user->getOnboardingEnabled()) {
            return false;
        }

        return true;
    }

    private function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
