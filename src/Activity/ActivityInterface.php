<?php

namespace App\Activity;

use App\Entity\Activity;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ActivityInterface
{
    /**
     * Returns the unique name of this activity type.
     * Example: projet_created, invite_user_on_project, user_added_fait_marquant, ...
     * String length must be <= 31.
     */
    public static function getType(): string;

    /**
     * Returns the filter type to render the activities with filter
     * in the dashboard
     * @return string
     */
    public static function getFilterType(): string;

    /**
     * Returns displayable text for this activity.
     *
     * @param array $activityParameters Parameters stored in Activity::$parameters
     * @param Activity $activity The Activity instance in case other fields are needed for rendering.
     *
     * @return string
     */
    public function render(array $activityParameters, Activity $activity): string;

    /**
     * Add constraints on activity parameters.
     */
    public function configureOptions(OptionsResolver $resolver): void;
}
