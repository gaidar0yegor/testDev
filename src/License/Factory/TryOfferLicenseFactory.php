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
class TryOfferLicenseFactory implements LicenseFactoryInterface
{
    public function createLicense(Societe $societe, DateTime $expirationDate = null): License
    {
        $license = new License();

        if (null === $expirationDate) {
            $expirationDate = (new DateTime())->modify('+3 months');
        }

        $license
            ->setName('Offre d\'essai')
            ->setDescription('Permet de tester RDI-Manager de manière illimitée pendant 3 mois.')
            ->setSociete($societe)
            ->setExpirationDate($expirationDate)
            ->setQuotas([
                ActiveProjetQuota::NAME => 1E3,
                ContributeurQuota::NAME => 1E3,
            ])
        ;

        return $license;
    }
}
