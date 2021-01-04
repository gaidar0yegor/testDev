<?php

namespace App\Service;

use App\Entity\Fichier;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FichierService
{
    private FilesystemInterface $storage;

    private FileResponseFactory $fileResponseFactory;

    public function __construct(FilesystemInterface $defaultStorage, FileResponseFactory $fileResponseFactory)
    {
        $this->storage = $defaultStorage;
        $this->fileResponseFactory = $fileResponseFactory;
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
        return $this->fileResponseFactory->createFileResponse(
            $this->storage->readStream('uploads/'.$fichier->getNomMd5()),
            $fichier->getNomFichier()
        );
    }

    public function delete(Fichier $fichier): void
    {
        $this->storage->delete('uploads/'.$fichier->getNomMd5());
    }
}
