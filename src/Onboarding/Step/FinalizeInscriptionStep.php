<?php

namespace App\Onboarding\Step;

use App\Entity\SocieteUser;
use App\Onboarding\OnboardingStepInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FinalizeInscriptionStep implements OnboardingStepInterface
{
    public function getText(): string
    {
        return 'Finalisez votre inscription';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, SocieteUser $societeUser): ?string
    {
        return '#';
    }

    public function isImportant(): bool
    {
        return true;
    }

    public function isCompleted(SocieteUser $societeUser): bool
    {
        return null === $societeUser->getInvitationToken();
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
