<?php

namespace App\Listener;

use App\Exception\InvitationTokenExpiredException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Catches InvitationTokenExpiredException
 * and display an explicit 404 error page.
 */
class InvitationTokenExpiredListener implements EventSubscriberInterface
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

        if (!$exception instanceof InvitationTokenExpiredException) {
            return;
        }

        $content = $this->twig->render('corp_app/invitation/invalid_invitation_token.html.twig', [
            'error' => $exception,
        ]);

        $response = (new Response())
            ->setStatusCode(Response::HTTP_NOT_FOUND)
            ->setContent($content)
        ;

        $event->setResponse($response);
    }
}
