<?php

namespace App\Activity;

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
     * Returns displayable text for this activity.
     *
     * @return string
     */
    public function render(array $activityParameters, string $activityType): string;

    /**
     * Add constraints on activity parameters.
     */
    public function configureOptions(OptionsResolver $resolver): void;
}
