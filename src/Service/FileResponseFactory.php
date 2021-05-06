<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileResponseFactory
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function createFileResponse($stream, string $filename, string $contentType = null): Response
    {
        return $this->createFileResponseFromString(stream_get_contents($stream), $filename, $contentType);
    }

    public function createFileResponseFromString(string $content, string $filename, string $contentType = null): Response
    {
        // Start the session now to prevent starting session after headers are sent by file response
        $this->session->start();

        $response = new StreamedResponse(
            function () use ($content) {
                echo $content;
                flush();
                exit;
            },
            200
        );

        $dispositionHeader = $response->headers->makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename);
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', strlen($content));

        if (null !== $contentType) {
            $response->headers->set('Content-Type', $contentType);
        }

        return $response;
    }
}
