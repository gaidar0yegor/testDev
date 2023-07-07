<?php

namespace App\Listener;

use App\Exception\InvitationTokenAlreadyHasSocieteException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Catches InvitationTokenAlreadyHasSocieteException
 * and display an explicit 404 error page.
 */
class InvitationTokenAlreadyHasSocieteListener implements EventSubscriberInterface
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof InvitationTokenAlreadyHasSocieteException) {
            return;
        }

        $content = $this->twig->render('corp_app/invitation/has_already_societe.html.twig', [
            'error' => $exception,
        ]);

        $response = (new Response())
            ->setStatusCode(Response::HTTP_NOT_FOUND)
            ->setContent($content)
        ;

        $event->setResponse($response);
    }
}
