<?php

namespace App\License\Factory;

use App\Entity\Societe;
use App\License\DTO\License;
use App\License\Quota\ActiveProjetQuota;
use App\License\Quota\ContributeurQuota;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Product\StarterProduct;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Create Starter license.
 */
class StarterLicenseFactory implements LicenseFactoryInterface
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
            $expirationDate = (new DateTime())->modify('+1 year');
        }

        $license
            ->setProductKey($this->getSocieteProductKey())
            ->setName("License {$this->getSocieteProductKey()}")
            ->setDescription('Cette license a été générée avec des fonctionnalités de base et rien de payant en termes de coût du service.')
            ->setSociete($societe)
            ->setExpirationDate($expirationDate)
            ->setQuotas([
                ActiveProjetQuota::NAME => 5,
                ContributeurQuota::NAME => 5,
            ])
        ;

        return $license;
    }

    /**
     * Get Societe Product Name
     */
    public function getSocieteProductKey(): string
    {
        return StarterProduct::PRODUCT_KEY;
    }
}
