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
 * Create a license from the "starter" offer (2 projets, 3 contributeurs)
 */
class StandardLicenseFactory implements LicenseFactoryInterface
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
            ->setDescription('Cette license a été générée avec des fonctionnalités standard.')
            ->setSociete($societe)
            ->setExpirationDate($expirationDate)
            ->setQuotas([
                ActiveProjetQuota::NAME => 1E3,
                ContributeurQuota::NAME => 5E3,
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
