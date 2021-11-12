<?php

namespace App\Twig;

use App\Entity\Fichier;
use App\Entity\FichierProjet;
use App\Exception\RdiException;
use App\Service\ExtensionToFontAwesomeIcon;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FileExtension extends AbstractExtension
{
    private ExtensionToFontAwesomeIcon $extensionToIcon;

    public const IMAGE_EXTENSIONS = ['AI', 'BMP', 'GIF', 'ICO', 'JPEG', 'JPG', 'PNG', 'PS', 'PSD', 'SVG', 'TIF', 'TIFF'];

    public function __construct(ExtensionToFontAwesomeIcon $extensionToIcon)
    {
        $this->extensionToIcon = $extensionToIcon;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('faFileIcon', [$this, 'faFileIcon']),
            new TwigFilter('isImageFile', [$this, 'isImageFile']),
        ];
    }

    /**
     * Guess which file icon to use from $fichier.
     */
    public function faFileIcon($fichier): string
    {
        if ($fichier instanceof Fichier) {
            return $this->extensionToIcon->getIconForFilename($fichier->getNomFichier());
        }

        if ($fichier instanceof FichierProjet) {
            return $this->extensionToIcon->getIconForFilename($fichier->getFichier()->getNomFichier());
        }

        throw new RdiException('Unsupported type passed to twig filter "faFileIcon"');
    }

    /**
     *  Check if $fichier is an image.
     */
    public function isImageFile($fichier): string
    {
        if ($fichier instanceof Fichier) {
            return in_array(strtoupper(pathinfo($fichier->getNomFichier(),PATHINFO_EXTENSION)),self::IMAGE_EXTENSIONS);
        }

        if ($fichier instanceof FichierProjet) {
            return in_array(strtoupper(pathinfo($fichier->getFichier()->getNomFichier(),PATHINFO_EXTENSION)),self::IMAGE_EXTENSIONS);
        }

        throw new RdiException('Unsupported type passed to twig filter "isImageFile"');
    }
}
