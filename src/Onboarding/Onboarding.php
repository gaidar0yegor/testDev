<?php

namespace App\Onboarding;

use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Onboarding
{
    /**
     * @var OnboardingStepInterface[]
     */
    private array $onboardingSteps;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(iterable $onboardingSteps, UrlGeneratorInterface $urlGenerator)
    {
        $this->onboardingSteps = iterator_to_array($onboardingSteps);
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return (string|bool)[][] Array of steps for an user, completed or not, like:
     *      [
     *        ['text' => 'Ajoutez vos projets', 'completed' => true],
     *        ['text' => 'Invitez vos users', 'completed' => false]
     *      ]
     */
    public function getStepsFor(User $user): array
    {
        return array_map(function (OnboardingStepInterface $step) use ($user) {
            return [
                'text' => $step->getText(),
                'link' => $step->getLink($this->urlGenerator, $user),
                'completed' => $step->isCompleted($user),
                'important' => $step->isImportant(),
            ];
        }, $this->onboardingSteps);
    }

    public function allCompleted(array $steps): bool
    {
        foreach ($steps as $step) {
            if (!$step['completed']) {
                return false;
            }
        }

        return true;
    }

    public function allImportantCompleted(array $steps): bool
    {
        foreach ($steps as $step) {
            if ($step['important'] && !$step['completed']) {
                return false;
            }
        }

        return true;
    }
}
