<?php

namespace App\Activity;

use App\Activity\Exception\UnimplementedActivityTypeException;
use App\Entity\Activity;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityService
{
    /**
     * @var ActivityInterface[]
     */
    private iterable $activityTypes;

    private ?ActivityInterface $fallbackActivityType;

    public function __construct(iterable $activityTypes)
    {
        $this->activityTypes = $activityTypes;
        $this->fallbackActivityType = null;
    }

    public function setFallbackActivityType(ActivityInterface $activityType): void
    {
        $this->fallbackActivityType = $activityType;
    }

    public function checkActivity(Activity $activity): void
    {
        $activityType = $this->loadActivityType($activity->getType());

        $this->checkParameters($activity->getParameters(), $activityType);
    }

    public function checkParameters(array $parameters, ActivityInterface $activityType): void
    {
        $optionsResolver = new OptionsResolver();

        $activityType->configureOptions($optionsResolver);

        $optionsResolver->resolve($parameters);
    }

    public function loadActivityType(string $type): ActivityInterface
    {
        foreach ($this->activityTypes as $key => $activityType) {
            if ($key === $type) {
                return $activityType;
            }
        }

        if (null === $this->fallbackActivityType) {
            throw new UnimplementedActivityTypeException($type);
        }

        return $this->fallbackActivityType;
    }

    /**
     * Returns a displayable text from an Activity
     */
    public function render(Activity $activity): string
    {
        return $this
            ->loadActivityType($activity->getType())
            ->render($activity->getParameters(), $activity)
        ;
    }
}
