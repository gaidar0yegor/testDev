<?php

namespace App\License\Factory;

use App\Entity\Societe;
use App\License\DTO\License;
use DateTime;

interface LicenseFactoryInterface
{
    /**
     * Create a license instance with predefined settings.
     * The license instance can then be used to generate a license file.
     */
    public function createLicense(Societe $societe, DateTime $expirationDate = null): License;

    /**
     * Get Societe Product Name
     */
    public function getSocieteProductKey(): string ;
}
