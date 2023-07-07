<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\Societe;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocieteLink implements EntityLinkGeneratorInterface
{
    public static function supportsEntity(): string
    {
        return Societe::class;
    }

    /**
     * @param Societe $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        return new EntityLink(
            $entity->getRaisonSociale(),
            $urlGenerator->generate('corp_app_bo_societe', [
                'id' => $entity->getId(),
            ])
        );
    }
}
