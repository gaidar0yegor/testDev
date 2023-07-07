<?php

namespace App\License\Factory;

use App\Entity\Societe;
use App\License\DTO\License;
use App\SocieteProduct\Product\PremiumProduct;
use App\SocieteProduct\Product\ProductPrivileges;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Create unlimited license for testing.
 */
class TryStandardLicenseFactory implements LicenseFactoryInterface
{
    private ProductPrivileges $productPrivileges;
    private TranslatorInterface $translator;
    private PremiumLicenseFactory $premiumLicenseFactory;

    public function __construct(ProductPrivileges $productPrivileges, PremiumLicenseFactory $premiumLicenseFactory, TranslatorInterface $translator)
    {
        $this->productPrivileges = $productPrivileges;
        $this->translator = $translator;
        $this->premiumLicenseFactory = $premiumLicenseFactory;
    }

    public function createLicense(Societe $societe, DateTime $expirationDate = null): License
    {
        $license = $this->premiumLicenseFactory->createLicense($societe, (new DateTime())->modify('+3 months'));

        $license
            ->setIsTryLicense(true)
            ->setName($license->getName() . ' / Offre d\'essai')
            ->setDescription('Permet de tester le journal collaboratif RDI-Manager pendant 3 mois.')
        ;

        return $license;
    }

    /**
     * Get Societe Product Name
     */
    public function getSocieteProductKey(): string
    {
        return PremiumProduct::PRODUCT_KEY;
    }
}
