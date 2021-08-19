<?php

namespace App\Onboarding;

use App\Entity\SocieteUser;
use RuntimeException;
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

    private function stepToArray(OnboardingStepInterface $step, SocieteUser $societeUser): array
    {
        return [
            'text' => $step->getText(),
            'link' => $step->getLink($this->urlGenerator, $societeUser),
            'completed' => $step->isCompleted($societeUser),
            'important' => $step->isImportant(),
        ];
    }

    /**
     * @return (string|bool)[][] Array of steps for an user, completed or not, like:
     *      [
     *        ['text' => 'Ajoutez vos projets', 'completed' => true],
     *        ['text' => 'Invitez vos users', 'completed' => false]
     *      ]
     */
    public function getStepsFor(SocieteUser $societeUser): array
    {
        return array_map(function (OnboardingStepInterface $step) use ($societeUser) {
            return $this->stepToArray($step, $societeUser);
        }, $this->onboardingSteps);
    }

    public function getStepFor(SocieteUser $societeUser, string $stepClassName): array
    {
        foreach ($this->onboardingSteps as $step) {
            if (get_class($step) === $stepClassName) {
                return $this->stepToArray($step, $societeUser);
            }
        }

        throw new RuntimeException(sprintf('No step "%s".', $stepClassName));
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
