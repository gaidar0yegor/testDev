<?php

namespace App\File\FileHandler;

use App\Entity\Fichier;
use App\File\FileHandlerInterface;
use App\File\FileResponseFactory;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestion du stockage des bannière et génération d'url publique.
 */
class EtudeBannerHandler implements FileHandlerInterface
{
    private FilesystemInterface $storage;

    private FileResponseFactory $fileResponseFactory;

    public string $filesAvatarUri;

    public string $defaultEtudeBannerUri;

    public function __construct(
        FilesystemInterface $avatarStorage,
        FileResponseFactory $fileResponseFactory,
        string $filesAvatarUri,
        string $defaultEtudeBannerUri
    ) {
        $this->storage = $avatarStorage;
        $this->fileResponseFactory = $fileResponseFactory;
        $this->filesAvatarUri = $filesAvatarUri;
        $this->defaultEtudeBannerUri = $defaultEtudeBannerUri;
    }

    public function getPublicUrl(Fichier $fichier = null): string
    {
        return $this->filesAvatarUri. ($fichier ? $fichier->getNomMd5() : '');
    }

    public function getDefaultBannerUrl(): string
    {
        return $this->defaultEtudeBannerUri;
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
