<?php

namespace App\License;

use App\Entity\Societe;

interface LicenseQuotaInterface
{
    /**
     * Returns the name of this quota, as described in license json data.
     */
    public function getName(): string;

    /**
     * Get the societe usage of the quota for this current quota.
     */
    public function calculateCurrentCount(Societe $societe): int;
}
