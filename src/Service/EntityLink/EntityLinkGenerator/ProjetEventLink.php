<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\FaitMarquant;
use App\Entity\ProjetEvent;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjetEventLink implements EntityLinkGeneratorInterface
{
    private const MAX_SIZE = 32;

    public static function supportsEntity(): string
    {
        return ProjetEvent::class;
    }

    /**
     * @param ProjetEvent $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        $titre = self::truncate($entity->getText());
        $idProjetEvent = $entity->getId();

        $url = $urlGenerator->generate('app_fo_projet_events', [
            'projetId' => $entity->getProjet()->getId(),
            'event' => $idProjetEvent,
        ]);

        return new EntityLink($titre, $url);
    }

    private static function truncate(string $s): string
    {
        if (mb_strlen($s) <= self::MAX_SIZE) {
            return $s;
        }

        return mb_substr($s, 0, self::MAX_SIZE).'…';
    }
}
