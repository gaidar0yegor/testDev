<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\FichierProjet;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FichierProjetLink implements EntityLinkGeneratorInterface
{
    public static function supportsEntity(): string
    {
        return FichierProjet::class;
    }

    /**
     * @param FichierProjet $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        return new EntityLink(
            $entity->getFichier()->getNomFichier(),
            $urlGenerator->generate('corp_app_fo_projet_fichier', [
                'projetId' => $entity->getProjet()->getId(),
                'fichierProjetId' => $entity->getId(),
            ])
        );
    }
}
