<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjetPlanningTaskLink implements EntityLinkGeneratorInterface
{
    public static function supportsEntity(): string
    {
        return ProjetPlanningTask::class;
    }

    /**
     * @param ProjetPlanningTask $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        return new EntityLink(
            $entity->getText(),
            $urlGenerator->generate('corp_app_fo_projet_planning', [
                'projetId' => $entity->getProjet()->getId(),
            ])
        );
    }
}
