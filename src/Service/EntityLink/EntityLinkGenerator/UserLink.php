<?php

namespace App\Service\EntityLink\EntityLinkGenerator;

use App\Entity\User;
use App\Service\EntityLink\EntityLink;
use App\Service\EntityLink\EntityLinkGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserLink implements EntityLinkGeneratorInterface
{
    public static function supportsEntity(): string
    {
        return User::class;
    }

    /**
     * @param User $entity
     */
    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink
    {
        return new EntityLink(
            $entity->getFullnameOrEmail(),
            $urlGenerator->generate('corp_app_fo_societe_user', [
                'id' => $entity->getId(),
            ])
        );
    }
}
