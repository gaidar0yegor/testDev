<?php

namespace App\Tests\Activity;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Activity type used for testing.
 * Just displays "activity_{id}" to run simple asserts.
 */
class TestActivityType implements ActivityInterface
{
    public static function getType(): string
    {
        return '_test';
    }

    public static function getFilterType(): string
    {
        return '_test';
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return 'activity_'.$activity->getId();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
