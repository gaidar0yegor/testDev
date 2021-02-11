<?php

namespace App\Onboarding\Step;

use App\Entity\User;
use App\Onboarding\OnboardingStepInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FinalizeInscriptionStep implements OnboardingStepInterface
{
    public function getText(): string
    {
        return 'Finalisez votre inscription';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, User $user): ?string
    {
        if (null === $user->getInvitationToken()) {
            return null;
        }

        return $urlGenerator->generate(
            'app_fo_user_finalize_inscription',
            [
                'token' => $user->getInvitationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isImportant(): bool
    {
        return true;
    }

    public function isCompleted(User $user): bool
    {
        return null === $user->getInvitationToken();
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
