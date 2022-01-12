<?php

namespace App\File;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileResponseFactory
{
    const MIME_TYPES = array(
      'txt' => 'text/plain',
      'htm' => 'text/html',
      'html' => 'text/html',
      'php' => 'text/html',
      'css' => 'text/css',
      'js' => 'application/javascript',
      'json' => 'application/json',
      'xml' => 'application/xml',
      'swf' => 'application/x-shockwave-flash',
      'flv' => 'video/x-flv',
      // images
      'png' => 'image/png',
      'jpe' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'gif' => 'image/gif',
      'bmp' => 'image/bmp',
      'ico' => 'image/vnd.microsoft.icon',
      'tiff' => 'image/tiff',
      'tif' => 'image/tiff',
      'svg' => 'image/svg+xml',
      'svgz' => 'image/svg+xml',
      // archives
      'zip' => 'application/zip',
      'rar' => 'application/x-rar-compressed',
      'exe' => 'application/x-msdownload',
      'msi' => 'application/x-msdownload',
      'cab' => 'application/vnd.ms-cab-compressed',
      // audio/video
      'mp3' => 'audio/mpeg',
      'qt' => 'video/quicktime',
      'mov' => 'video/quicktime',
      // adobe
      'pdf' => 'application/pdf',
      'psd' => 'image/vnd.adobe.photoshop',
      'ai' => 'application/postscript',
      'eps' => 'application/postscript',
      'ps' => 'application/postscript',
      // ms office
      'doc' => 'application/msword',
      'rtf' => 'application/rtf',
      'xls' => 'application/vnd.ms-excel',
      'ppt' => 'application/vnd.ms-powerpoint',
      'docx' => 'application/msword',
      'xlsx' => 'application/vnd.ms-excel',
      'pptx' => 'application/vnd.ms-powerpoint',
      // open office
      'odt' => 'application/vnd.oasis.opendocument.text',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    public function createFileResponse($stream, string $filename, bool $toDownload = false, string $contentType = null): Response
    {
        if (!$toDownload && key_exists(pathinfo($filename)['extension'],$this::MIME_TYPES)){
            return $this->createFileViewerResponseFromString(stream_get_contents($stream), $filename, $this::MIME_TYPES[pathinfo($filename)['extension']]);
        } else {
            return $this->createFileResponseFromString(stream_get_contents($stream), $filename, $contentType);
        }
    }

    public function createFileViewerResponseFromString(string $content, string $filename, string $contentType = null): Response
    {
        $response = new StreamedResponse(
            function () use ($content) {
                echo $content;
            },
            200
        );

        $filenameFallback = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
        $filenameFallback = str_replace('%', '', $filenameFallback);

        $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_INLINE . '; filename="' . $filenameFallback . '"');

        if (null !== $contentType) {
            $response->headers->set('Content-Type', $contentType);
        }

        return $response->send();
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
