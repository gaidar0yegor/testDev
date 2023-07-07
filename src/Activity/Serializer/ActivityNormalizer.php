<?php

namespace App\Activity\Serializer;

use App\Activity\ActivityService;
use App\Entity\Activity;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActivityNormalizer implements NormalizerInterface
{
    private ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function normalize($activity, string $format = null, array $context = [])
    {
        if (!$activity instanceof Activity) {
            throw new InvalidArgumentException();
        }

        return [
            'datetime' => $activity->getDatetime(),
            'type' => $activity->getType(),
            'parameters' => $activity->getParameters(),
            'rendered' => $this->activityService->render($activity),
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Activity;
    }
}
