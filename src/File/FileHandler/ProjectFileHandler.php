<?php

namespace App\File\FileHandler;

use App\Entity\Fichier;
use App\Entity\FichierProjet;
use App\File\FileHandlerInterface;
use App\File\FileResponseFactory;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Gestion des fichiers privés uploadés sur un projet.
 */
class ProjectFileHandler implements FileHandlerInterface
{
    private FilesystemInterface $storage;

    private FileResponseFactory $fileResponseFactory;

    private KernelInterface $appKernel;

    public function __construct(FilesystemInterface $projectFilesStorage, FileResponseFactory $fileResponseFactory, KernelInterface $appKernel)
    {
        $this->storage = $projectFilesStorage;
        $this->fileResponseFactory = $fileResponseFactory;
        $this->appKernel = $appKernel;
    }

    public function upload(FichierProjet $fichierProjet): void
    {
        $fichier = $fichierProjet->getFichier();

        $fichier->setDefaultFilename();

        if (null !== $fichier->getFile()){
            $filename = $fichierProjet->getRelativeFilePath();
            $stream = fopen($fichier->getFile()->getRealPath(), 'r+');

            $this->storage->writeStream($filename, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function replace(FichierProjet $fichierProjet, string $oldRelativeFilePath): void
    {
        $this->storage->rename($oldRelativeFilePath, $fichierProjet->getRelativeFilePath());
    }

    public function createDownloadResponse(FichierProjet $fichierProjet, bool $toDownload = false): Response
    {
        return $this->fileResponseFactory->createFileResponse(
            $this->storage->readStream($fichierProjet->getRelativeFilePath()),
            $fichierProjet->getFichier()->getNomFichier(),
            $toDownload
        );
    }

    public function delete(FichierProjet $fichierProjet): void
    {
        if ($this->storage->has($fichierProjet->getRelativeFilePath())){
            $this->storage->delete($fichierProjet->getRelativeFilePath());
        }
    }
}
