<?php

namespace App\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

/**
 * Catch les erreurs de l'application pour les afficher correctement
 * avec le template RDI, puis la raison des erreurs 400,
 * ou un message global pour les erreurs 500.
 */
class KernelExceptionListener
{
    private $twig;

    private $logger;

    public function __construct(Environment $twig, LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $this->logger->critical($exception->getMessage(), [
            'exception' => $exception,
        ]);

        $response = new Response();

        if ($exception instanceof HttpExceptionInterface) {
            $response->headers->replace($exception->getHeaders());

            $content = $this->twig->render('error/error_4xx.html.twig', [
                'error' => $exception,
            ]);

            $response
                ->setStatusCode($exception->getStatusCode())
                ->setContent($content)
            ;
        } else {
            $content = $this->twig->render('error/error_5xx.html.twig');

            $response
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
                ->setContent($content)
            ;
        }

        $event->setResponse($response);
    }
}
