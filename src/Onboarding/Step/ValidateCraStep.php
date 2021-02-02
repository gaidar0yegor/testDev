<?php

namespace App\Onboarding\Step;

use App\Entity\User;
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
        return 'Saisissez vos temps passés sur vos projets';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, User $user): ?string
    {
        return $urlGenerator->generate(
            'app_fo_temps',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isCompleted(User $user): bool
    {
        return null !== $this
            ->craRepository
            ->findOneBy([
                'user' => $user,
            ])
        ;
    }

    public static function getPriority(): int
    {
        return 30;
    }
}
