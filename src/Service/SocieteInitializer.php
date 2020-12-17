<?php

namespace App\Service;

use App\DTO\InitSociete;
use App\Entity\Societe;
use App\Entity\User;
use App\Exception\RdiException;

class SocieteInitializer
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
        $admin = new User();

        $invitationToken = $this->tokenGenerator->generateUrlToken();

        $societe
            ->setRaisonSociale($initSociete->getRaisonSociale())
            ->addUser($admin)
        ;

        $admin
            ->setEmail($initSociete->getAdminEmail())
            ->setRole('ROLE_FO_ADMIN')
            ->setInvitationToken($invitationToken)
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

        $this->societeNotificationsService->persistAll($notifications);
    }
}
