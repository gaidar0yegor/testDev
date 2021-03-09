<?php

namespace App\License;

use App\Entity\Societe;
use App\Entity\User;
use App\Exception\UnexpectedUserException;
use App\HasSocieteInterface;
use App\License\DTO\License;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private LicenseService $licenseService;

    private QuotaService $quotaService;

    private Security $security;

    public function __construct(LicenseService $licenseService, QuotaService $quotaService, Security $security)
    {
        $this->licenseService = $licenseService;
        $this->quotaService = $quotaService;
        $this->security = $security;
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
        if (null === $hasSociete) {
            $hasSociete = $this->security->getUser();

            if (!$hasSociete instanceof User) {
                throw new UnexpectedUserException($hasSociete);
            }
        }

        return $this->licenseService->isSameSociete($license, $hasSociete);
    }

    public function hasActiveLicense(Societe $societe): bool
    {
        return count($this->licenseService->retrieveAllActiveLicenses($societe));
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
