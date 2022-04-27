<?php

namespace App\License\Quota;

use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\License\DTO\Quota;
use App\License\Exception\LicenseQuotaReachedException;
use App\License\LicenseQuotaInterface;
use App\License\LicenseService;
use App\Notification\Event\OverflowQuotasBoNotification;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleProjet;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContributeurQuota implements LicenseQuotaInterface
{
    public const NAME = 'contributeurs';

    private LicenseService $licenseService;

    private SocieteUserRepository $societeUserRepository;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        LicenseService $licenseService,
        SocieteUserRepository $societeUserRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->licenseService = $licenseService;
        $this->societeUserRepository = $societeUserRepository;
        $this->dispatcher = $dispatcher;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function calculateCurrentCount(Societe $societe): int
    {
        return count(
            $this->societeUserRepository->findUsersWithAtLeastOneRoleOnProjets($societe, RoleProjet::CONTRIBUTEUR)
        );
    }

    public function checkAddNewContributeur(Societe $societe, ProjetParticipant $projetParticipant = null): void
    {
        // Ignore if societe is not yet persisted
        if (null === $societe->getId()) {
            return;
        }

        // Ignore if adding/updating an observateur
        if (
            null !== $projetParticipant
            && null !== ($participantRole = $projetParticipant->getRole())
            && !RoleProjet::hasRole($participantRole, RoleProjet::CONTRIBUTEUR)
        ) {
            return;
        }

        $contributeurs = $this
            ->societeUserRepository
            ->findUsersWithAtLeastOneRoleOnProjets($societe, RoleProjet::CONTRIBUTEUR)
        ;
        $limit = $this->licenseService->calculateSocieteMaxQuota($societe, self::NAME);
        $quotaAfter = new Quota(count($contributeurs), $limit);

        if (null === $projetParticipant || !in_array($projetParticipant->getSocieteUser(), $contributeurs)) {
            $quotaAfter->increment();
        }

        if ($quotaAfter->isOverflow()) {
            $this->dispatcher->dispatch(new OverflowQuotasBoNotification($societe, 'Contributeur'));
            throw new LicenseQuotaReachedException(self::NAME, $quotaAfter);
        }
    }

    public function prePersist(ProjetParticipant $projetParticipant, LifecycleEventArgs $args): void
    {
        $this->checkAddNewContributeur($projetParticipant->getSociete(), $projetParticipant);
    }

    public function preUpdate(ProjetParticipant $projetParticipant, LifecycleEventArgs $args): void
    {
        $societe = $projetParticipant->getSociete();

        if (null === $societe) {
            return;
        }

        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($projetParticipant);

        if (!isset($changes['role'])) {
            return;
        }

        $this->checkAddNewContributeur($societe, $projetParticipant);
    }
}
