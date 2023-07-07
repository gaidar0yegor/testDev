<?php

namespace App\License\Listener;

use App\License\Exception\LicenseException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Catches all LicenseException to display them as a displayable message.
 */
class LicenseExceptionListener implements EventSubscriberInterface
{
    private FlashBagInterface $flashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onQuotaException',
        ];
    }

    public function onQuotaException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();

        if (!$throwable instanceof LicenseException) {
            return;
        }

        $this->flashBag->add('warning', $throwable->getMessage());

        $event->setResponse(new RedirectResponse($event->getRequest()->getUri()));
    }
}
