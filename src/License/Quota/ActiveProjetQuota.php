<?php

namespace App\License\Quota;

use App\Entity\Projet;
use App\Entity\Societe;
use App\License\DTO\Quota;
use App\License\Exception\LicenseQuotaReachedException;
use App\License\LicenseQuotaInterface;
use App\License\LicenseService;
use App\Notification\Event\OverflowQuotasBoNotification;
use App\Repository\ProjetRepository;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Exception;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActiveProjetQuota implements LicenseQuotaInterface
{
    public const NAME = 'activeProjet';

    private LicenseService $licenseService;

    private ProjetRepository $projetRepository;

    private EventDispatcherInterface $dispatcher;

    public function __construct(LicenseService $licenseService, ProjetRepository $projetRepository, EventDispatcherInterface $dispatcher)
    {
        $this->licenseService = $licenseService;
        $this->projetRepository = $projetRepository;
        $this->dispatcher = $dispatcher;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function calculateCurrentCount(Societe $societe): int
    {
        return count($this->projetRepository->findActiveProjetForSociete($societe));
    }

    public function checkAddNewActiveProjet(Societe $societe, Projet $projet = null): void
    {
        // Ignore if societe is not yet persisted
        if (null === $societe->getId()) {
            return;
        }

        // Ingore if adding/updated a projet not yet started or already finished
        if (null !== $projet && !$projet->isProjetActiveInDate(new DateTime())) {
            return;
        }

        $activeProjets = $this
            ->projetRepository
            ->findActiveProjetForSociete($societe)
        ;
        $limit = $this->licenseService->calculateSocieteMaxQuota($societe, self::NAME);
        $quotaAfter = new Quota(count($activeProjets), $limit);

        if (null === $projet || !in_array($projet, $activeProjets)) {
            $quotaAfter->increment();
        }

        if ($quotaAfter->isOverflow()) {
            $this->dispatcher->dispatch(new OverflowQuotasBoNotification($societe, 'Projet'));
            throw new LicenseQuotaReachedException(self::NAME, $quotaAfter);
        }
    }

    public function prePersist(Projet $projet, LifecycleEventArgs $args): void
    {
        $this->checkAddNewActiveProjet($projet->getSociete(), $projet);
    }

    public function preUpdate(Projet $projet, LifecycleEventArgs $args): void
    {
        $societe = $projet->getSociete();
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($projet);

        // Ignore if start/end dates have not been updated
        if (!isset($changes['dateDebut']) && !isset($changes['dateFin'])) {
            return;
        }

        $this->checkAddNewActiveProjet($societe, $projet);
    }
}
