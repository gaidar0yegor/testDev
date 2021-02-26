<?php

namespace App\LicenseGeneration\DefaultLicense;

use App\Entity\Societe;
use App\License\DTO\License;

class FreeLicenseFactory implements DefaultLicenseFactoryInterface
{
    public function createDefaultLicense(Societe $societe): License
    {
        return License::createFreeLicenseFor($societe);
    }
}
