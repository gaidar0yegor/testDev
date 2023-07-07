<?php

namespace App\License;

use App\Entity\Societe;
use App\HasSocieteInterface;
use App\License\DTO\License;
use App\License\Serializer\LicenseDecoder;
use DateTime;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Loads licenses files and calculates max quotas for a societe.
 */
class LicenseService
{
    private SerializerInterface $serializer;

    private FilesystemInterface $licensesStorage;

    public function __construct(
        SerializerInterface $serializer,
        FilesystemInterface $licensesStorage
    ) {
        $this->serializer = $serializer;
        $this->licensesStorage = $licensesStorage;
    }

    public function parseLicenseContent(string $licenseContent): License
    {
        return $this->serializer->deserialize($licenseContent, License::class, LicenseDecoder::FORMAT);
    }

    public function parseLicenseFile(string $licenseFilename): License
    {
        return $this->parseLicenseContent(
            $this->readLicenseFile($licenseFilename)
        );
    }

    /**
     * Store license file in storage, in corresponding societe folder.
     */
    public function storeLicense(string $licenseContent): void
    {
        $license = $this->parseLicenseContent($licenseContent);
        $societeUuid = $license->getSociete()->getUuid();
        $fileUniqueName = $license->getExpirationDate()->format('Ymd').'-'.dechex(rand(0x100000, 0xFFFFFF));

        $this->removeAllLicenses($license->getSociete());
        $this->licensesStorage->write("$societeUuid/rdi-manager-license-$fileUniqueName.txt", $licenseContent);
    }

    public function readLicenseFile(string $path): string
    {
        return $this->licensesStorage->read($path);
    }

    /**
     * @return License[] Indexed by filename.
     */
    public function retrieveAllLicenses(Societe $societe): array
    {
        $licenses = [];
        $licenseFiles = $this->licensesStorage->listContents($societe->getUuid()->__toString());

        foreach ($licenseFiles as $licenseFile) {
            $licenseContent = $this->licensesStorage->read($licenseFile['path']);

            $licenses[$licenseFile['path']] = $this->parseLicenseContent($licenseContent);
        }

        $this->sortLicenses($licenses, $societe);

        return $licenses;
    }

    /**
     * @return License[]
     */
    public function retrieveAllActiveLicenses(Societe $societe): array
    {
        return array_filter($this->retrieveAllLicenses($societe), function (License $license) use ($societe) {
            return $this->isActiveForSociete($license, $societe);
        });
    }

    public function getLicenseExpirationDate(Societe $societe): DateTime
    {
        $expirationDate = null;
        foreach ($this->retrieveAllLicenses($societe) as $license){
            $expirationDate =  $license->getExpirationDate();
        }

        return $expirationDate;
    }

    /**
     * Remove all licenses of a societe.
     */
    public function removeAllLicenses(Societe $societe): void
    {
        $this->licensesStorage->deleteDir($societe->getUuid());
    }

    /**
     * Sort licenses by active first, then most recent first.
     */
    public function sortLicenses(array &$licenses, Societe $societe): void
    {
        uasort($licenses, function (License $a, License $b) use ($societe) {
            $scoreA = $a->getExpirationDate()->getTimestamp();
            $scoreB = $b->getExpirationDate()->getTimestamp();

            if (!$this->isActiveForSociete($a, $societe)) {
                $scoreA -= 1E10;
            }

            if (!$this->isActiveForSociete($b, $societe)) {
                $scoreB -= 1E10;
            }

            return $scoreB - $scoreA;
        });
    }

    /*
     * check if the license is for try offer or not
     */
    public function checkHasTryLicense(Societe $societe): bool
    {
        $licenses = $this->retrieveAllLicenses($societe);
        $isTryLicense = false;
        foreach ($licenses as $license) {
            $isTryLicense = $license->getIsTryLicense();
        }

        return $isTryLicense;
    }

    /*
     * check if the license is for try offer expired or not
     */
    public function checkHasTryLicenseExpired(Societe $societe): bool
    {
        $licenses = $this->retrieveAllLicenses($societe);
        $isTryLicenseExpired = false;
        foreach ($licenses as $license) {
            if ($license->getIsTryLicense()){
                $isTryLicenseExpired = $this->isExpired($license);
            }
        }

        return $isTryLicenseExpired;
    }

    public function calculateSocieteMaxQuota(Societe $societe, string $quotaName): int
    {
        $licenses = $this->retrieveAllActiveLicenses($societe);
        $maxQuota = 0;

        foreach ($licenses as $license) {
            if (isset($license->getQuotas()[$quotaName])) {
                $maxQuota = max($license->getQuotas()[$quotaName], $maxQuota);
            }
        }

        return $maxQuota;
    }

    /**
     * Calculate max quotas for all quotas from societe active licenses.
     *
     * @return int[]
     */
    public function calculateSocieteMaxQuotas(Societe $societe): array
    {
        $licenses = $this->retrieveAllActiveLicenses($societe);
        $quotas = [];

        foreach ($licenses as $license) {
            foreach ($license->getQuotas() as $quotaName => $quotaValue) {
                if (!isset($quotas[$quotaName])) {
                    $quotas[$quotaName] = 0;
                }

                $quotas[$quotaName] = max($quotas[$quotaName], $quotaValue);
            }
        }

        return $quotas;
    }

    /**
     * Whether the license can currently be used for $societe
     */
    public function isActiveForSociete(License $license, HasSocieteInterface $hasSociete): bool
    {
        return !$this->isExpired($license) && $this->isSameSociete($license, $hasSociete);
    }

    public function isExpired(License $license): bool
    {
        return (new DateTime())->modify('-1 day') > $license->getExpirationDate();
    }

    public function isSameSociete(License $license, HasSocieteInterface $hasSociete): bool
    {
        $societe = $hasSociete->getSociete();

        return 0 === $license->getSociete()->getUuid()->compareTo($societe->getUuid());
    }
}
