<?php

namespace App\Activity;

use App\Activity\Exception\UnimplementedActivityHandlerException;
use App\Entity\Activity;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityService implements EventSubscriber
{
    /**
     * @var ActivityHandlerInterface[]
     */
    private iterable $activityHandlers;

    private ?ActivityHandlerInterface $fallbackActivityHandler;

    public function __construct(iterable $activityHandlers)
    {
        $this->activityHandlers = $activityHandlers;
        $this->fallbackActivityHandler = null;
    }

    public function checkActivity(Activity $activity): void
    {
        $activityHandler = $this->loadActivityHandler($activity->getType());

        $this->checkParameters($activity->getParameters(), $activityHandler);
    }

    public function checkParameters(array $parameters, ActivityHandlerInterface $activityHandler): void
    {
        $optionsResolver = new OptionsResolver();

        $activityHandler->configureOptions($optionsResolver);

        $optionsResolver->resolve($parameters);
    }

    public function setFallbackActivityHandler(ActivityHandlerInterface $activityHandler): void
    {
        $this->fallbackActivityHandler = $activityHandler;
    }

    public function loadActivityHandler(string $type): ActivityHandlerInterface
    {
        foreach ($this->activityHandlers as $key => $activityHandler) {
            if ($key === $type) {
                return $activityHandler;
            }
        }

        if (null === $this->fallbackActivityHandler) {
            throw new UnimplementedActivityHandlerException($type);
        }

        return $this->fallbackActivityHandler;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->entityEvent(ActivityEvent::CREATED, $args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->entityEvent(ActivityEvent::UPDATED, $args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->entityEvent(ActivityEvent::REMOVED, $args);
    }

    public function entityEvent(string $eventType, LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $entityClass = get_class($entity);
        $em = $args->getEntityManager();

        foreach ($this->activityHandlers as $activityHandler) {
            list($listenerEntityType, $listenerEventType) = $activityHandler->getSubscribedEvent();

            if ($listenerEventType !== $eventType) {
                continue;
            }

            if ($listenerEntityType !== $entityClass) {
                continue;
            }

            $activity = $activityHandler->onEvent($entity, $em);

            if (null === $activity) {
                continue;
            }

            $this->checkParameters($activity->getParameters(), $activityHandler);
        }
    }
}
