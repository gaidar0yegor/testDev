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

    public function __construct(ExtensionToFontAwesomeIcon $extensionToIcon)
    {
        $this->extensionToIcon = $extensionToIcon;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('faFileIcon', [$this, 'faFileIcon']),
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
}
