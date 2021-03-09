<?php

namespace App\LicenseGeneration\Listener;

use App\Entity\Societe;
use App\License\Factory\LicenseFactoryInterface;
use App\License\Factory\TryOfferLicenseFactory;
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

    private LicenseFactoryInterface $licenseFactory;

    private FlashBagInterface $flashBag;

    public function __construct(
        LicenseService $licenseService,
        LicenseGeneration $licenseGeneration,
        FlashBagInterface $flashBag
    ) {
        $this->licenseService = $licenseService;
        $this->licenseGeneration = $licenseGeneration;
        $this->flashBag = $flashBag;

        $this->licenseFactory = new TryOfferLicenseFactory();
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
            $license = $this->licenseFactory->createLicense($societe);

            $licenseContent = $this->licenseGeneration->generateLicenseFile($license);

            $this->licenseService->storeLicense($licenseContent);
        } catch (EncryptionKeysException $e) {
            $this->flashBag->add('danger', 'Impossible de générer une license gratuite par défaut.');
        }
    }
}
