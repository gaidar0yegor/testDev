<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\Evenement;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EvenementLink implements EntityLinkGeneratorInterface
{
    public const MAX_SIZE = 32;

    public static function supportsEntity(): string
    {
        return Evenement::class;
    }

    /**
     * @param Evenement $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        $titre = self::truncate($entity->getText());
        $idEvenement = $entity->getId();

        $url = $urlGenerator->generate('corp_app_fo_current_user_events', [
            'event' => $idEvenement,
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
