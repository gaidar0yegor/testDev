<?php

namespace App\ObservateurExterne;

use App\Entity\ProjetObservateurExterne;
use App\ObservateurExterne\Notification\InvitationObservateurExterneNotification;
use App\Service\TokenGenerator;
use DateTime;
use LogicException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InvitationService
{
    private TokenGenerator $tokenGenerator;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        TokenGenerator $tokenGenerator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->dispatcher = $dispatcher;
    }

    public function sendInvitation(ProjetObservateurExterne $projetObservateurExterne): void
    {
        if (null === $projetObservateurExterne->getProjet()) {
            throw new LogicException('Projet must be known before invite on projet');
        }

        if (null !== $projetObservateurExterne->getUser()) {
            throw new LogicException('An user already accepted this invitation');
        }

        $projetObservateurExterne
            ->setInvitationToken($this->tokenGenerator->generateUrlToken())
            ->setInvitationSentAt(new DateTime())
        ;

        $this->dispatcher->dispatch(new InvitationObservateurExterneNotification($projetObservateurExterne));
    }
}
