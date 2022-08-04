<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\FaitMarquant;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FaitMarquantLink implements EntityLinkGeneratorInterface
{
    private const MAX_SIZE = 32;

    public static function supportsEntity(): string
    {
        return FaitMarquant::class;
    }

    /**
     * @param FaitMarquant $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        $titre = self::truncate($entity->getTitre());
        $idFaitMarquant = $entity->getId();

        $url = $urlGenerator->generate('corp_app_fo_projet', [
            'id' => $entity->getProjet()->getId(),
            '_fragment' => "fait-marquant-$idFaitMarquant",
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
