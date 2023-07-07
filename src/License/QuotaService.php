<?php

namespace App\License;

use App\Entity\Societe;
use App\License\DTO\Quota;

/**
 * Calculate current used quota for a societe, and checks overflow.
 */
class QuotaService
{
    private LicenseService $licenseService;

    /**
     * @var LicenseQuotaInterface[]
     */
    private iterable $licenseQuotas;

    public function __construct(
        LicenseService $licenseService,
        iterable $licenseQuotas
    ) {
        $this->licenseService = $licenseService;
        $this->licenseQuotas = $licenseQuotas;
    }

    /**
     * Calculates current quota used by a societe.
     *
     * @return Quota[]
     */
    public function calculateSocieteCurrentQuotas(Societe $societe): array
    {
        /** @var Quota[] */
        $societeQuotas = array_map(
            function (int $maxQuota) {
                return (new Quota())->setLimit($maxQuota);
            },
            $this->licenseService->calculateSocieteMaxQuotas($societe)
        );

        foreach ($this->licenseQuotas as $licenseQuota) {
            if (!isset($societeQuotas[$licenseQuota->getName()])) {
                $societeQuotas[$licenseQuota->getName()] = new Quota();
            }

            $societeQuotas[$licenseQuota->getName()]->setCurrent($licenseQuota->calculateCurrentCount($societe));
        }

        return $societeQuotas;
    }

    /**
     * @return Quota[] Array of quota that are overflow.
     */
    public function getOverflowQuotas(Societe $societe): array
    {
        return array_filter(
            $this->calculateSocieteCurrentQuotas($societe),
            function (Quota $quota) {
                return $quota->isOverflow();
            }
        );
    }

    /**
     * Returns whether $societe has a quota overflow,
     * which can happen when all licenses expired.
     */
    public function hasQuotaOverflow(Societe $societe): bool
    {
        foreach ($this->calculateSocieteCurrentQuotas($societe) as $quota) {
            if ($quota->isOverflow()) {
                return true;
            }
        }

        return false;
    }
}
