<?php

namespace App\MultiPlateform\Listener;

use App\Entity\User;
use App\MultiSociete\UserContext;
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
class SinglePlateformListener implements EventSubscriberInterface
{
    private EntityManagerInterface $em;
    private UserContext $userContext;

    public function __construct(EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->userContext = $userContext;
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

        if (
            (null === $user->getCurrentSocieteUser() && null !== $user->getCurrentUserBook()) ||
            (null !== $user->getCurrentSocieteUser() && null === $user->getCurrentUserBook())
        ) {
            return;
        }

        if (null !== $user->getCurrentSocieteUser() && null !== $user->getCurrentUserBook())
        {
            $this->userContext->disconnectSociete();
            $this->userContext->disconnectUserLabo();
            $this->em->flush();
            return;
        }

        if (1 === count($user->getEnabledSocieteUsers()) && 0 === count($user->getUserBooks())) {
            $user->setCurrentSocieteUser($user->getSocieteUsers()->first());
            $this->em->flush();
            return;
        }

        if (0 === count($user->getEnabledSocieteUsers()) && 1 === count($user->getUserBooks())) {
            $user->setCurrentUserBook($user->getUserBooks()->first());
            $this->em->flush();
            return;
        }
    }
}
