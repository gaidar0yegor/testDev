<?php

namespace App\ObservateurExterne;

use App\Entity\Projet;
use App\Entity\ProjetObservateurExterne;
use App\Entity\SocieteUser;
use App\Exception\RdiException;
use App\ObservateurExterne\Notification\InvitationObservateurExterneNotification;
use App\Security\Role\RoleProjet;
use App\Service\TokenGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InvitationService
{
    private TokenGenerator $tokenGenerator;

    private EventDispatcherInterface $dispatcher;

    private EntityManagerInterface $em;

    public function __construct(
        TokenGenerator $tokenGenerator,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $em
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->dispatcher = $dispatcher;
        $this->em = $em;
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

    public function sendAutomaticInvitationSurProjet(Projet $projet, string $invitationEmail): ProjetObservateurExterne
    {
        $projetObservateurExterne = new ProjetObservateurExterne();
        $projetObservateurExterne->setProjet($projet);
        $projetObservateurExterne->setInvitationEmail($invitationEmail);
        $this->sendInvitation($projetObservateurExterne);

        $this->em->persist($projetObservateurExterne);
        $this->em->flush();

        return $projetObservateurExterne;
    }
}
