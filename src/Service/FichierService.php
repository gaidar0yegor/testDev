<?php

namespace App\Service;

use App\Entity\Fichier;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FichierService
{
    private FilesystemInterface $storage;

    public function __construct(FilesystemInterface $defaultStorage)
    {
        $this->storage = $defaultStorage;
    }

    public function upload(Fichier $fichier): void
    {
        $fileName = md5(uniqid()).'.'.$fichier->getFile()->guessExtension();

        $fichier
            ->setNomFichier($fichier->getFile()->getClientOriginalName())
            ->setNomMd5($fileName)
        ;

        $stream = fopen($fichier->getFile()->getRealPath(), 'r+');
        $this->storage->writeStream("uploads/$fileName", $stream);
        fclose($stream);
    }

    public function createDownloadResponse(Fichier $fichier): Response
    {
        $stream = $this->storage->readStream('uploads/'.$fichier->getNomMd5());

        return new StreamedResponse(function () use ($stream) {
            echo stream_get_contents($stream);
            flush();
        }, 200, [
            'Content-Transfer-Encoding', 'binary',
            'Content-Disposition' => sprintf(
                'attachment; filename="%s"',
                $fichier->getNomFichier()
            ),
            'Content-Length' => fstat($stream)['size'],
        ]);
    }

    public function delete(Fichier $fichier): void
    {
        $this->storage->delete('uploads/'.$fichier->getNomMd5());
    }
}
