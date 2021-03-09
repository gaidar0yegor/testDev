<?php

namespace App\LicenseGeneration\DefaultLicense;

use App\Entity\Societe;
use App\License\DTO\License;

interface DefaultLicenseFactoryInterface
{
    public function createDefaultLicense(Societe $societe): License;
}
