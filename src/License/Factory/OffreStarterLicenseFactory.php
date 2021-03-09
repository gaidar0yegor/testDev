<?php

namespace App\License\Factory;

use App\Entity\Societe;
use App\License\DTO\License;
use App\License\Quota\ActiveProjetQuota;
use App\License\Quota\ContributeurQuota;
use DateTime;

/**
 * Create a license from the "starter" offer (2 projets, 3 contributeurs)
 */
class OffreStarterLicenseFactory implements LicenseFactoryInterface
{
    public function createLicense(Societe $societe, DateTime $expirationDate = null): License
    {
        $license = new License();

        if (null === $expirationDate) {
            $expirationDate = (new DateTime())->modify('+6 months');
        }

        $license
            ->setName('Offre starter')
            ->setDescription('Permet d\'utiliser gratuitement RDI-Manager à petite échelle.')
            ->setSociete($societe)
            ->setExpirationDate($expirationDate)
            ->setQuotas([
                ActiveProjetQuota::NAME => 2,
                ContributeurQuota::NAME => 3,
            ])
        ;

        return $license;
    }
}
