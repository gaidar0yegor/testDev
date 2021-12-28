<?php

namespace App\File;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileResponseFactory
{
    public function createFileResponse($stream, string $filename, string $contentType = null): Response
    {
        return $this->createFileResponseFromString(stream_get_contents($stream), $filename, $contentType);
    }

    public function createFileResponseFromString(string $content, string $filename, string $contentType = null): Response
    {
        $response = new StreamedResponse(
            function () use ($content) {
                echo $content;
                flush();

                // exit now to prevent sending headers twice and getting silent critical error in logs,
                // like "headers already sent"
                exit;
            },
            200,
            [
                ResponseHeaderBag::DISPOSITION_INLINE
            ]
        );

        $filenameFallback = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
        $filenameFallback = str_replace('%', '', $filenameFallback);

        $dispositionHeader = $response->headers->makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
            $filenameFallback
        );
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', strlen($content));

        if (null !== $contentType) {
            $response->headers->set('Content-Type', $contentType);
        }

        return $response;
    }
}
