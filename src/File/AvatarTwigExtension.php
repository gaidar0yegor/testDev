<?php

namespace App\File;

use App\Entity\Societe;
use App\Entity\User;
use App\File\FileHandler\AvatarHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AvatarTwigExtension extends AbstractExtension
{
    private AvatarHandler $avatarHandler;

    public function __construct(AvatarHandler $avatarHandler)
    {
        $this->avatarHandler = $avatarHandler;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('avatarUrl', [$this, 'avatarUrl']),
            new TwigFilter('logoUrl', [$this, 'logoUrl']),
        ];
    }

    public function avatarUrl(User $user): string
    {
        $fichier = $user->getAvatar();

        if (null === $fichier) {
            return '';
        }

        return $this->avatarHandler->getPublicUrl($fichier);
    }

    public function logoUrl(Societe $societe): string
    {
        $fichier = $societe->getLogo();

        if (null === $fichier) {
            return '';
        }

        return $this->avatarHandler->getPublicUrl($fichier);
    }
}
