<?php

namespace App\License\Listener;

use App\Entity\Cra;
use App\Entity\FaitMarquant;
use App\Entity\FichierProjet;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\ProjetPlanning;
use App\Entity\ProjetPlanningTask;
use App\Entity\TempsPasse;
use App\HasSocieteInterface;
use App\License\Exception\OverflowQuotasException;
use App\License\QuotaService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use LogicException;
use RuntimeException;

/**
 * Makes access read-only when all licenses expired.
 * Deletions are still possible.
 */
class LicenseExpiredListener implements EventSubscriber
{
    private QuotaService $quotaService;

    public function __construct(QuotaService $quotaService)
    {
        $this->quotaService = $quotaService;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->checkLicensesExpiration($args->getObject());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->checkLicensesExpiration($args->getObject());
    }

    public function checkLicensesExpiration($entity): void
    {
        $readOnlyEntities = [
            Cra::class,
            FaitMarquant::class,
            FichierProjet::class,
            TempsPasse::class,
            ProjetPlanning::class,
            ProjetPlanningTask::class,
            Event::class,
            EventParticipant::class,
        ];

        if (!in_array(get_class($entity), $readOnlyEntities, true)) {
            return;
        }

        if (!$entity instanceof HasSocieteInterface) {
            throw new LogicException('License expiration check can only be performed on HasSocieteInterface entity.');
        }

        $societe = $entity->getSociete();

        // New societe, don't check quotas because cannot retrieve quotas
        if (null === $societe->getId()) {
            return;
        }

        if (null === $societe) {
            throw new RuntimeException('Cannot retrieve societe from HasSocieteInterface: returned null.');
        }

        if (count($quotas = $this->quotaService->getOverflowQuotas($societe)) > 0) {
            throw new OverflowQuotasException($quotas);
        }
    }
}
