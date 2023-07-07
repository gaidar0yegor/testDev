<?php

namespace App\Activity;

use App\Entity\Activity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function getFilters(): array
    {
        return [
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderActivity', [$this, 'renderActivity'], ['is_safe' => ['html']]),
        ];
    }

    public function renderActivity(Activity $activity): string
    {
        return $this->activityService->render($activity);
    }
}
