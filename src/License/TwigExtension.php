<?php

namespace App\License;

use App\Entity\Societe;
use App\HasSocieteInterface;
use App\License\DTO\License;
use App\MultiSociete\UserContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private LicenseService $licenseService;

    private QuotaService $quotaService;

    private UserContext $userContext;

    public function __construct(LicenseService $licenseService, QuotaService $quotaService, UserContext $userContext)
    {
        $this->licenseService = $licenseService;
        $this->quotaService = $quotaService;
        $this->userContext = $userContext;
    }

    public function getFilters(): array
    {
        return [
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isLicenseExpired', [$this, 'isLicenseExpired']),
            new TwigFunction('isLicenseSameSociete', [$this, 'isLicenseSameSociete']),
            new TwigFunction('hasActiveLicense', [$this, 'hasActiveLicense']),
            new TwigFunction('getLicenseExpirationDate', [$this, 'getLicenseExpirationDate']),
            new TwigFunction('hasTryLicense', [$this, 'hasTryLicense']),
            new TwigFunction('hasQuotaOverflow', [$this, 'hasQuotaOverflow']),
            new TwigFunction('usedQuotas', [$this, 'usedQuotas']),
        ];
    }

    public function isLicenseExpired(License $license): bool
    {
        return $this->licenseService->isExpired($license);
    }

    public function isLicenseSameSociete(License $license, HasSocieteInterface $hasSociete = null): bool
    {
        return $this->licenseService->isSameSociete($license, $hasSociete ?? $this->userContext->getSocieteUser());
    }

    public function hasActiveLicense(Societe $societe): bool
    {
        return count($this->licenseService->retrieveAllActiveLicenses($societe));
    }

    public function getLicenseExpirationDate(Societe $societe): \DateTime
    {
        return $this->licenseService->getLicenseExpirationDate($societe);
    }

    public function hasTryLicense(Societe $societe): bool
    {
        return $this->licenseService->checkHasTryLicense($societe);
    }

    public function hasQuotaOverflow(Societe $societe): bool
    {
        return $this->quotaService->hasQuotaOverflow($societe);
    }

    public function usedQuotas(Societe $societe): array
    {
        return $this->quotaService->calculateSocieteCurrentQuotas($societe);
    }
}
