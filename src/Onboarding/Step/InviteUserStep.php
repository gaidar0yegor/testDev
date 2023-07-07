<?php

namespace App\Onboarding\Step;

use App\Entity\SocieteUser;
use App\Onboarding\OnboardingStepInterface;
use App\Repository\SocieteUserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InviteUserStep implements OnboardingStepInterface
{
    private SocieteUserRepository $societeUserRepository;

    public function __construct(SocieteUserRepository $societeUserRepository)
    {
        $this->societeUserRepository = $societeUserRepository;
    }

    public function getText(): string
    {
        return 'Invitez vos collaborateurs';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, SocieteUser $societeUser): ?string
    {
        return $urlGenerator->generate(
            'corp_app_fo_admin_user_invite',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isImportant(): bool
    {
        return true;
    }

    public function isCompleted(SocieteUser $societeUser): bool
    {
        return count($this->societeUserRepository->findBySameSociete($societeUser)) > 1;
    }

    public static function getPriority(): int
    {
        return 50;
    }
}
