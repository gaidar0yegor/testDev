<?php

namespace App\MultiUserBook\Listener;

use App\MultiUserBook\Exception\NoCurrentUserBookException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens to NoCurrentUserBookException to redirect user
 * to UserBooks switching page instead of displaying an error.
 */
class NoCurrentUserBookExceptionListener implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    private FlashBagInterface $flashBag;

    public function __construct(UrlGeneratorInterface $urlGenerator, FlashBagInterface $flashBag)
    {
        $this->urlGenerator = $urlGenerator;
        $this->flashBag = $flashBag;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Check previous exceptions in case it is catched and redispatched (like Twig does)
        do {
            if ($exception instanceof NoCurrentUserBookException) {
                $this->redirectToSwitchUserBookPage($event);
            }
        } while (null !== ($exception = $exception->getPrevious()));
    }

    private function redirectToSwitchUserBookPage(ExceptionEvent $event): void
    {
        $url = $this->urlGenerator->generate('corp_app_fo_multi_societe_switch', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $event->setResponse(new RedirectResponse($url));
        $this->flashBag->add('warning', 'Vous devez séléctionner votre cahier de laboratoire pour accéder à cette page.');
    }
}
