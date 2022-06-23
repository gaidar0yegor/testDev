<?php

namespace App\File;

use App\Entity\LabApp\Etude;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FichierEtudeTwigExtension extends AbstractExtension
{
    private string $etudeFileStorageUri;
    private string $defaultEtudeBannerUrl;

    public function __construct(string $etudeFileStorageUri, string $defaultEtudeBannerUrl)
    {
        $this->etudeFileStorageUri = $etudeFileStorageUri;
        $this->defaultEtudeBannerUrl = $defaultEtudeBannerUrl;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('bannerUrl', [$this, 'bannerUrl'])
        ];
    }


    public function bannerUrl(Etude $etude): string
    {
        if (null === $etude->getBanner() || null === $etude->getBanner()->getFichier()) {
            return $this->defaultEtudeBannerUrl;
        }

//        return $this->;
    }
}
