<?php

namespace App\Listener;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\KernelEvents;

class UseCacheListener implements EventSubscriberInterface
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!is_array($controller)) {
            return;
        }

        list($controllerObject, $methodName) = $controller;

        $reflectionClass = new ReflectionClass(get_class($controllerObject));
        $useCache = $this->reader->getClassAnnotation($reflectionClass, UseCache::class);

        if (null === $useCache) {
            $useCache = $this->reader->getMethodAnnotation($reflectionClass->getMethod($methodName), UseCache::class);
        }

        if (null !== $useCache) {
            $event->getRequest()->attributes->set('_rdi_use_cache', true);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->getRequest()->attributes->get('_rdi_use_cache')) {
            $event
                ->getResponse()
                ->headers
                ->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, true)
            ;
        }
    }
}
