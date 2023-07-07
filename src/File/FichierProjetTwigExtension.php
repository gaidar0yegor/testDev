<?php

namespace App\File;

use App\Entity\FichierProjet;
use App\Service\FichierProjetService;
use App\MultiSociete\UserContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FichierProjetTwigExtension extends AbstractExtension
{
    private FichierProjetService $fichierProjetService;

    private UserContext $userContext;

    public function __construct(FichierProjetService $fichierProjetService ,UserContext $userContext)
    {
        $this->fichierProjetService = $fichierProjetService;
        $this->userContext = $userContext;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isAccessibleFichierProjet', [$this, 'isAccessibleFichierProjet']),
        ];
    }

    public function isAccessibleFichierProjet(FichierProjet $fichierProjet) :bool
    {
        return $this->fichierProjetService->isAccessibleFichierProjet($fichierProjet);
    }
}
