<?php

namespace App\License\Factory;

use App\Entity\Societe;
use App\License\DTO\License;
use App\License\Quota\ActiveProjetQuota;
use App\License\Quota\ContributeurQuota;
use DateTime;

/**
 * Create unlimited license for testing.
 */
class UnlimitedLicenseFactory implements LicenseFactoryInterface
{
    public function createLicense(Societe $societe, DateTime $expirationDate = null): License
    {
        $license = new License();

        if (null === $expirationDate) {
            $expirationDate = (new DateTime())->modify('+1 day');
        }

        $license
            ->setName('License illimitée')
            ->setDescription('Cette license a été générée pour développer ou lancer les tests automatisés.')
            ->setSociete($societe)
            ->setExpirationDate($expirationDate)
            ->setQuotas([
                ActiveProjetQuota::NAME => 1E6,
                ContributeurQuota::NAME => 1E6,
            ])
        ;

        return $license;
    }
}
