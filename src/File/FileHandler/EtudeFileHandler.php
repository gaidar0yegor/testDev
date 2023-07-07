<?php

namespace App\File\FileHandler;

use App\Entity\LabApp\FichierEtude;
use App\File\FileHandlerInterface;
use App\File\FileResponseFactory;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Gestion des fichiers privés uploadés sur une étude.
 */
class EtudeFileHandler implements FileHandlerInterface
{
    private FilesystemInterface $storage;

    private FileResponseFactory $fileResponseFactory;

    private KernelInterface $appKernel;

    public function __construct(FilesystemInterface $etudeFilesStorage, FileResponseFactory $fileResponseFactory, KernelInterface $appKernel)
    {
        $this->storage = $etudeFilesStorage;
        $this->fileResponseFactory = $fileResponseFactory;
        $this->appKernel = $appKernel;
    }

    public function upload(FichierEtude $fichierEtude): void
    {
        $fichier = $fichierEtude->getFichier();

        $fichier->setDefaultFilename();

        $filename = $fichierEtude->getRelativeFilePath();
        $stream = fopen($fichier->getFile()->getRealPath(), 'r+');

        $this->storage->writeStream($filename, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    public function replace(FichierEtude $fichierEtude, string $oldRelativeFilePath): void
    {
        $this->storage->rename($oldRelativeFilePath, $fichierEtude->getRelativeFilePath());
    }

    public function createDownloadResponse(FichierEtude $fichierEtude, bool $toDownload = false): Response
    {
        return $this->fileResponseFactory->createFileResponse(
            $this->storage->readStream($fichierEtude->getRelativeFilePath()),
            $fichierEtude->getFichier()->getNomFichier(),
            $toDownload
        );
    }

    public function delete(FichierEtude $fichierEtude): void
    {
        $this->storage->delete($fichierEtude->getRelativeFilePath());
    }
}
