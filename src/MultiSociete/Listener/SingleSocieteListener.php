<?php

namespace App\MultiSociete\Listener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * When an user have access to only one societe,
 * automatically switch him to this societe.
 *
 * This prevents forcing the user to switch to a societe
 * if he has only one.
 *
 * It is also useful whe user is creating his societe through "/creer_ma-societe"
 * has it prevents redirect him on switch societes page.
 */
class SingleSocieteListener implements EventSubscriberInterface
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

        // User has already switched to a societe
        if (null !== $user->getCurrentSocieteUser()) {
            return;
        }

        // User has 0 or multiple societe, so cannot guess which societe to switch on
        if (1 !== count($user->getEnabledSocieteUsers())) {
            return;
        }

        $user->setCurrentSocieteUser($user->getSocieteUsers()->first());

        $this->em->flush();
    }
}
