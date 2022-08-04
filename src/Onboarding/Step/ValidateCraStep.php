<?php

namespace App\Onboarding\Step;

use App\Entity\SocieteUser;
use App\Onboarding\OnboardingStepInterface;
use App\Repository\CraRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ValidateCraStep implements OnboardingStepInterface
{
    private CraRepository $craRepository;

    public function __construct(CraRepository $craRepository)
    {
        $this->craRepository = $craRepository;
    }

    public function getText(): string
    {
        return 'Suivi de temps';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, SocieteUser $societeUser): ?string
    {
        return $urlGenerator->generate(
            'corp_app_fo_temps',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isImportant(): bool
    {
        return false;
    }

    public function isCompleted(SocieteUser $societeUser): bool
    {
        return null !== $this
            ->craRepository
            ->findOneBy([
                'societeUser' => $societeUser,
            ])
        ;
    }

    public static function getPriority(): int
    {
        return 30;
    }
}
