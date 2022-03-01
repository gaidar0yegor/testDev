<?php

namespace App\License\Factory;

use App\Entity\Societe;
use App\License\DTO\License;
use App\License\Quota\ActiveProjetQuota;
use App\License\Quota\ContributeurQuota;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Product\StandardProduct;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Create unlimited license for testing.
 */
class TryStandardLicenseFactory implements LicenseFactoryInterface
{
    private ProductPrivileges $productPrivileges;
    private TranslatorInterface $translator;

    public function __construct(ProductPrivileges $productPrivileges, TranslatorInterface $translator)
    {
        $this->productPrivileges = $productPrivileges;
        $this->translator = $translator;
    }

    public function createLicense(Societe $societe, DateTime $expirationDate = null): License
    {
        $license = new License();

        if (null === $expirationDate) {
            $expirationDate = (new DateTime())->modify('+3 months');
        }

        $license
            ->setIsTryLicense(true)
            ->setProductKey($this->getSocieteProductKey())
            ->setName('Offre d\'essai')
            ->setDescription('Permet de tester le pack Standard de RDI-Manager pendant 3 mois.')
            ->setSociete($societe)
            ->setExpirationDate($expirationDate)
            ->setQuotas([
                ActiveProjetQuota::NAME => 10,
                ContributeurQuota::NAME => 49,
            ])
        ;

        return $license;
    }

    /**
     * Get Societe Product Name
     */
    public function getSocieteProductKey(): string
    {
        return StandardProduct::PRODUCT_KEY;
    }
}
