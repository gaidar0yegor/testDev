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

    private string $etudeFileStorageUri;

    public function __construct(
        FilesystemInterface $projectFilesStorage,
        FileResponseFactory $fileResponseFactory,
        KernelInterface $appKernel,
        string $etudeFileStorageUri
    )
    {
        $this->storage = $projectFilesStorage;
        $this->fileResponseFactory = $fileResponseFactory;
        $this->appKernel = $appKernel;
        $this->etudeFileStorageUri = $etudeFileStorageUri;
    }

    public function upload(FichierEtude $fichierEtude): void
    {
        $fichier = $fichierEtude->getFichier();

        $fichier->setDefaultFilename();

        $filename = "lab/" . $fichierEtude->getRelativeFilePath();
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
