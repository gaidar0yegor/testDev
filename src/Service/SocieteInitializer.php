<?php

namespace App\Service;

use App\DTO\InitSociete;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\Entity\User;
use App\Exception\RdiException;
use App\Security\Role\RoleSociete;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

class SocieteInitializer implements EventSubscriber
{
    private TokenGenerator $tokenGenerator;

    private SocieteNotificationsService $societeNotificationsService;

    public function __construct(
        TokenGenerator $tokenGenerator,
        SocieteNotificationsService $societeNotificationsService
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->societeNotificationsService = $societeNotificationsService;
    }

    public function initializeSociete(InitSociete $initSociete): Societe
    {
        $societe = new Societe();
        $societeUser = new SocieteUser();

        $invitationToken = $this->tokenGenerator->generateUrlToken();

        $societe
            ->setRaisonSociale($initSociete->getRaisonSociale())
            ->addSocieteUser($societeUser)
        ;

        $societeUser
            ->setSociete($societe)
            ->setRole(RoleSociete::ADMIN)
            ->setInvitationEmail($initSociete->getAdminEmail())
            ->setInvitationToken($invitationToken)
            ->addSocieteUserPeriod(new SocieteUserPeriod())
        ;

        return $societe;
    }

    public function initializeCronJobs(Societe $societe): void
    {
        if (null === $societe->getId()) {
            throw new RdiException('Societe must be persisted before creating notifications');
        }

        $notifications = $this->societeNotificationsService->createInitialSocieteNotifications($societe);

        $notifications->enableAll();
        $notifications->setSmsEnabled(true);

        $this->societeNotificationsService->persistAll($societe, $notifications);
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(Societe $societe): void
    {
        $this->initializeCronJobs($societe);
    }
}
