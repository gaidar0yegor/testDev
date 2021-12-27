<?php

namespace App\File\FileHandler;

use App\Entity\Fichier;
use App\Entity\FichierProjet;
use App\File\FileHandlerInterface;
use App\File\FileResponseFactory;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestion des fichiers privés uploadés sur un projet.
 */
class ProjectFileHandler implements FileHandlerInterface
{
    private FilesystemInterface $storage;

    private FileResponseFactory $fileResponseFactory;

    public function __construct(FilesystemInterface $projectFilesStorage, FileResponseFactory $fileResponseFactory)
    {
        $this->storage = $projectFilesStorage;
        $this->fileResponseFactory = $fileResponseFactory;
    }

    public function upload(FichierProjet $fichierProjet): void
    {
        $fichier = $fichierProjet->getFichier();

        $fichier->setDefaultFilename();

        $filename = $fichierProjet->getRelativeFilePath();
        $stream = fopen($fichier->getFile()->getRealPath(), 'r+');

        $this->storage->writeStream($filename, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    public function replace(FichierProjet $fichierProjet, string $oldRelativeFilePath): void
    {
        $this->storage->rename($oldRelativeFilePath, $fichierProjet->getRelativeFilePath());
    }

    public function createDownloadResponse(FichierProjet $fichierProjet): Response
    {
        return $this->fileResponseFactory->createFileResponse(
            $this->storage->readStream($fichierProjet->getRelativeFilePath()),
            $fichierProjet->getFichier()->getNomFichier()
        );
    }

    public function delete(Fichier $fichier): void
    {
        $this->storage->delete($fichier->getNomMd5());
    }
}
