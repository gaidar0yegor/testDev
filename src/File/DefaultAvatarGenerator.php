<?php

namespace App\File;

use App\Entity\Fichier;
use App\Entity\User;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use League\Flysystem\FilesystemInterface;

class DefaultAvatarGenerator
{
    private FilesystemInterface $avatarStorage;

    public function __construct(FilesystemInterface $avatarStorage)
    {
        $this->avatarStorage = $avatarStorage;
    }

    public function generateDefaultAvatarFor(User $user): Fichier
    {
        $avatar = new InitialAvatar();

        $colors = [
            '#1f77b4',
            '#ff7f0e',
            '#2ca02c',
            '#9467bd',
        ];

        $color = $colors[rand(0, 3)];

        $image = $avatar
            ->background($color)
            ->color('#ffffff')
            ->size(512)
            ->generate($user->getFullname())
            ->stream()
        ;

        $fichier = new Fichier();

        $fichier
            ->setNomMd5(md5(uniqid()).'.jpg')
            ->setNomFichier('default-avatar-user-'.$user->getId().'-'.md5(uniqid()).'.jpg')
        ;

        $this->avatarStorage->write($fichier->getNomMd5(), $image);

        return $fichier;
    }
}
