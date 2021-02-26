<?php

namespace App\LicenseGeneration\DefaultLicense;

use App\Entity\Societe;
use App\License\DTO\License;
use DateTime;

class TestingLicenseFactory implements DefaultLicenseFactoryInterface
{
    /**
     * Create unlimited license for testing.
     */
    public function createDefaultLicense(Societe $societe): License
    {
        return License::createUnlimitedLicense($societe, (new DateTime())->modify('+1 day'));
    }
}
