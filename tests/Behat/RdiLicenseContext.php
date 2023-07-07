<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\License\DTO\License;
use App\License\LicenseService;
use App\License\Quota\ActiveProjetQuota;
use App\License\Quota\ContributeurQuota;
use App\LicenseGeneration\LicenseGeneration;
use App\Repository\SocieteRepository;
use App\SocieteProduct\Product\PremiumProduct;
use Behat\MinkExtension\Context\RawMinkContext;
use DateTime;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class RdiLicenseContext extends RawMinkContext
{
    private LicenseGeneration $licenseGeneration;

    private LicenseService $licenseService;

    private SocieteRepository $societeRepository;

    public function __construct(
        LicenseGeneration $licenseGeneration,
        LicenseService $licenseService,
        SocieteRepository $societeRepository
    ) {
        $this->licenseGeneration = $licenseGeneration;
        $this->licenseService = $licenseService;
        $this->societeRepository = $societeRepository;
    }

    /**
     * @Given societe :raisonSociale reset licenses
     */
    public function iResetMyLicenses($raisonSociale)
    {
        $societe = $this->societeRepository->findOneBy([
            'raisonSociale' => $raisonSociale,
        ]);

        $this->licenseService->removeAllLicenses($societe);
    }

    /**
     * @Given societe :raisonSociale has a license with :quotaProjet projets and :quotaContributeurs contributeurs
     */
    public function iHaveALicenseWithNProjetsAndMContributeurs($raisonSociale, $quotaProjet, $quotaContributeurs)
    {
        $societe = $this->societeRepository->findOneBy([
            'raisonSociale' => $raisonSociale,
        ]);

        $license = new License();

        $license
            ->setProductKey(PremiumProduct::PRODUCT_KEY)
            ->setName('Unlimited license for dev or test')
            ->setSociete($societe)
            ->setExpirationDate((new DateTime())->modify('+1 day'))
            ->setQuotas([
                ActiveProjetQuota::NAME => $quotaProjet,
                ContributeurQuota::NAME => $quotaContributeurs,
            ])
        ;

        $licenseContent = $this->licenseGeneration->generateLicenseFile($license);
        $this->licenseService->storeLicense($licenseContent);
    }

    /**
     * @Given societe :raisonSociale has an expired license
     */
    public function iHaveAnExpiredLicense($raisonSociale)
    {
        $societe = $this->societeRepository->findOneBy([
            'raisonSociale' => $raisonSociale,
        ]);

        $license = new License();

        $license
            ->setProductKey(PremiumProduct::PRODUCT_KEY)
            ->setName('Unlimited license for dev or test, yes, but expired')
            ->setSociete($societe)
            ->setExpirationDate((new DateTime())->modify('-6 months'))
            ->setQuotas([
                ActiveProjetQuota::NAME => 10000,
                ContributeurQuota::NAME => 10000,
            ])
        ;

        $licenseContent = $this->licenseGeneration->generateLicenseFile($license);
        $this->licenseService->storeLicense($licenseContent);
    }
}
