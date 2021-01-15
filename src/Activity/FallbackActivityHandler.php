<?php

namespace App\Activity;

use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FallbackActivityHandler implements ActivityHandlerInterface
{
    public static function getType(): string
    {
        return '_fallback';
    }

    public function render(array $activityParameters): string
    {
        return 'Fallback activity: '.print_r($activityParameters, true);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function getSubscribedEvent(): array
    {
        return [];
    }

    public function onEvent($entity, EntityManagerInterface $em): ?Activity
    {
        return null;
    }
}
