<?php

namespace App\LicenseGeneration\Listener;

use App\Entity\Societe;
use App\License\Factory\LicenseFactoryInterface;
use App\License\Factory\TryStandardLicenseFactory;
use App\License\LicenseService;
use App\LicenseGeneration\Exception\EncryptionKeysException;
use App\LicenseGeneration\LicenseGeneration;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Automatically adds a free license to newly created societe.
 */
class SocieteCreatedListener
{
    private LicenseService $licenseService;

    private LicenseGeneration $licenseGeneration;

    private TryStandardLicenseFactory $tryStandardLicenseFactory;

    private FlashBagInterface $flashBag;

    public function __construct(
        LicenseService $licenseService,
        LicenseGeneration $licenseGeneration,
        FlashBagInterface $flashBag,
        TryStandardLicenseFactory $tryStandardLicenseFactory
    ) {
        $this->licenseService = $licenseService;
        $this->licenseGeneration = $licenseGeneration;
        $this->flashBag = $flashBag;
        $this->tryStandardLicenseFactory = $tryStandardLicenseFactory;
    }

    /**
     * Optionnal dependency injection setter for default license factory.
     */
    public function setDefaultLicenseFactory(LicenseFactoryInterface $licenseFactory): void
    {
        $this->licenseFactory = $licenseFactory;
    }

    public function postPersist(Societe $societe, LifecycleEventArgs $args): void
    {
        try {
            $license = $this->tryStandardLicenseFactory->createLicense($societe);

            $licenseContent = $this->licenseGeneration->generateLicenseFile($license);

            $this->licenseService->storeLicense($licenseContent);

            if ($this->tryStandardLicenseFactory instanceof TryStandardLicenseFactory){
                $societe->setProductKey($license->getProductKey());
                $args->getEntityManager()->persist($societe);
                $args->getEntityManager()->flush();
            }
        } catch (EncryptionKeysException $e) {
            $this->flashBag->add('error', 'Impossible de générer une license gratuite par défaut.');
        }
    }
}
