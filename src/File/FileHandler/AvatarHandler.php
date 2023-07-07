<?php

namespace App\File\FileHandler;

use App\Entity\Fichier;
use App\File\FileHandlerInterface;
use App\File\FileResponseFactory;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestion du stockage des avatar et génération d'url publique.
 */
class AvatarHandler implements FileHandlerInterface
{
    private FilesystemInterface $storage;

    private FileResponseFactory $fileResponseFactory;

    public string $filesAvatarUri;

    public function __construct(
        FilesystemInterface $avatarStorage,
        FileResponseFactory $fileResponseFactory,
        string $filesAvatarUri
    ) {
        $this->storage = $avatarStorage;
        $this->fileResponseFactory = $fileResponseFactory;
        $this->filesAvatarUri = $filesAvatarUri;
    }

    public function getPublicUrl(Fichier $fichier = null): string
    {
        return $this->filesAvatarUri. ($fichier ? $fichier->getNomMd5() : '');
    }

    public function upload(Fichier $fichier): void
    {
        $fichier->setDefaultFilename();

        $filename = $fichier->getNomMd5();
        $stream = fopen($fichier->getFile()->getRealPath(), 'r+');

        $this->storage->writeStream($filename, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    public function createDownloadResponse(Fichier $fichier): Response
    {
        return $this->fileResponseFactory->createFileResponse(
            $this->storage->readStream($fichier->getNomMd5()),
            $fichier->getNomFichier()
        );
    }

    public function delete(Fichier $fichier): void
    {
        $this->storage->delete($fichier->getNomMd5());
    }
}
