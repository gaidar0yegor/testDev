<?php

namespace App\MultiPlateform\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use App\MultiSociete\UserContext;

class MultiPlateformListener implements EventSubscriberInterface {

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

    public function onLogin(InteractiveLoginEvent $event)
    {
        if (count($this->userContext->getUser()->getSocieteUsers()) > 1)
        {
            $this->userContext->disconnectSociete();
            $this->em->flush();
        }

        if (count($this->userContext->getUser()->getUserBooks()) > 1)
        {
            $this->userContext->disconnectUserLabo();
            $this->em->flush();
        }


    }
}