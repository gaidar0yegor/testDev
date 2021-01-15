<?php

namespace App\Activity;

use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ActivityHandlerInterface
{
    /**
     * Returns the activity type this handler supports.
     * Example: projet_created, invite_user_on_project, user_added_fait_marquant, ...
     * String length must be <= 31.
     */
    public static function getType(): string;

    /**
     * Get doctrine event to listen to.
     * Example: [Projet::class, ActivityListener::CREATED]
     */
    public function getSubscribedEvent(): array;

    /**
     * Method called on doctrine event from listenEvent().
     * Create and persist new activity item, with optional relations.
     * Returns created Activity instance.
     */
    public function onEvent($entity, EntityManagerInterface $em): ?Activity;

    /**
     * Returns displayable text for this activity.
     *
     * @return string
     */
    public function render(array $activityParameters): string;

    /**
     * Add constraints on activity parameters.
     */
    public function configureOptions(OptionsResolver $resolver): void;
}
