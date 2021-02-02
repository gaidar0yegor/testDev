<?php

namespace App\Onboarding;

use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface OnboardingStepInterface
{
    /**
     * @return string Text to display in onboarding tasks list item
     */
    public function getText(): string;

    /**
     * @return string|null Optional absolute url (absolute is required for mails) to complete this step.
     *                     Should not be called if the step is completed.
     */
    public function getLink(UrlGeneratorInterface $urlGenerator, User $user): ?string;

    /**
     * @return bool Whether this onboarding step has been done already for this user
     */
    public function isCompleted(User $user): bool;

    /**
     * @return int Order of the step in the tasks list
     */
    public static function getPriority(): int;
}
