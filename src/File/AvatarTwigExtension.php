<?php

namespace App\File;

use App\Entity\LabApp\Etude;
use App\Entity\Societe;
use App\Entity\User;
use App\File\FileHandler\AvatarHandler;
use App\File\FileHandler\EtudeBannerHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AvatarTwigExtension extends AbstractExtension
{
    private AvatarHandler $avatarHandler;
    private EtudeBannerHandler $etudeBannerHandler;

    public function __construct(AvatarHandler $avatarHandler, EtudeBannerHandler $etudeBannerHandler)
    {
        $this->avatarHandler = $avatarHandler;
        $this->etudeBannerHandler = $etudeBannerHandler;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('avatarUrl', [$this, 'avatarUrl']),
            new TwigFilter('logoUrl', [$this, 'logoUrl']),
            new TwigFilter('bannerUrl', [$this, 'bannerUrl']),
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

    public function bannerUrl(Etude $etude): string
    {
        $fichier = $etude->getBanner();

        if (null === $fichier) {
            return $this->etudeBannerHandler->getDefaultBannerUrl();
        }

        return $this->etudeBannerHandler->getPublicUrl($fichier);
    }
}
