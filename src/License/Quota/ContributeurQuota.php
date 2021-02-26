<?php

namespace App\License\Quota;

use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\License\DTO\Quota;
use App\License\Exception\LicenseQuotaReachedException;
use App\License\LicenseQuotaInterface;
use App\License\LicenseService;
use App\Repository\UserRepository;
use App\Role;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ContributeurQuota implements LicenseQuotaInterface
{
    public const NAME = 'contributeurs';

    private LicenseService $licenseService;

    private UserRepository $userRepository;

    public function __construct(
        LicenseService $licenseService,
        UserRepository $userRepository
    ) {
        $this->licenseService = $licenseService;
        $this->userRepository = $userRepository;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function calculateCurrentCount(Societe $societe): int
    {
        return count($this->userRepository->findUsersWithAtLeastOneRoleOnProjets($societe, Role::CONTRIBUTEUR));
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
            && !Role::hasRole($participantRole, Role::CONTRIBUTEUR)
        ) {
            return;
        }

        $contributeurs = $this->userRepository->findUsersWithAtLeastOneRoleOnProjets($societe, Role::CONTRIBUTEUR);
        $limit = $this->licenseService->calculateSocieteMaxQuota($societe, self::NAME);
        $quotaAfter = new Quota(count($contributeurs), $limit);

        if (null === $projetParticipant || !in_array($projetParticipant->getUser(), $contributeurs)) {
            $quotaAfter->increment();
        }

        if ($quotaAfter->isOverflow()) {
            throw new LicenseQuotaReachedException(self::NAME, $quotaAfter);
        }
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
