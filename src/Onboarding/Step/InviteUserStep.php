<?php

namespace App\Onboarding\Step;

use App\Entity\User;
use App\Onboarding\OnboardingStepInterface;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InviteUserStep implements OnboardingStepInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getText(): string
    {
        return 'Invitez vos collaborateurs';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, User $user): ?string
    {
        return $urlGenerator->generate(
            'app_fo_admin_user_invite',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isImportant(): bool
    {
        return true;
    }

    public function isCompleted(User $user): bool
    {
        return count($this->userRepository->findBySameSociete($user)) > 1;
    }

    public static function getPriority(): int
    {
        return 50;
    }
}
