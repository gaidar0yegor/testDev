<?php

namespace App\Activity;

use App\Entity\Activity;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FallbackActivityType implements ActivityInterface
{
    public static function getType(): string
    {
        return '_fallback';
    }

    public static function getFilterType(): string
    {
        return '_fallback';
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        $activityType = $activity->getType();

        return "Unexpected activity type: '$activityType', parameters: ".print_r($activityParameters, true);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
