<?php

namespace App\MultiSociete\Listener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Listener to check user current societeUser when he logins.
 *
 * It should disconnect user from societe when its access has been disabled by admin.
 */
class CheckCurrentSocieteOnLoginListener implements EventSubscriberInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        if (null === $user->getCurrentSocieteUser()) {
            return;
        }

        if (!$user->getCurrentSocieteUser()->getEnabled()) {
            $user->setCurrentSocieteUser(null);
        }

        $this->em->flush();
    }
}
