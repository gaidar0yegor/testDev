<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\Projet;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjetLink implements EntityLinkGeneratorInterface
{
    public static function supportsEntity(): string
    {
        return Projet::class;
    }

    /**
     * @param Projet $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        return new EntityLink(
            $entity->getAcronyme(),
            $urlGenerator->generate('corp_app_fo_projet', [
                'id' => $entity->getId(),
            ])
        );
    }
}
