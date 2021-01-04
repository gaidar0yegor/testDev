<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileResponseFactory
{
    public function createFileResponse($stream, string $filename, string $contentType = null): Response
    {
        $headers = [
            'Content-Transfer-Encoding', 'binary',
            'Content-Disposition' => sprintf(
                'attachment; filename="%s"',
                $filename
            ),
            'Content-Length' => fstat($stream)['size'],
        ];

        if (null !== $contentType) {
            $headers['Content-Type'] = $contentType;
        }

        return new StreamedResponse(
            function () use ($stream) {
                echo stream_get_contents($stream);
                flush();
            },
            200,
            $headers
        );
    }
}
