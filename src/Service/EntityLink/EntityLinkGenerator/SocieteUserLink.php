<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\SocieteUser;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocieteUserLink implements EntityLinkGeneratorInterface
{
    public static function supportsEntity(): string
    {
        return SocieteUser::class;
    }

    /**
     * @param SocieteUser $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        $user = $entity->getUser();

        if (null === $user) {
            return new EntityLink('en cours d\'invitation', '#');
        }

        return new EntityLink(
            $user->getFullnameOrEmail(),
            $urlGenerator->generate('corp_app_fo_societe_user', [
                'id' => $entity->getId(),
            ])
        );
    }
}
