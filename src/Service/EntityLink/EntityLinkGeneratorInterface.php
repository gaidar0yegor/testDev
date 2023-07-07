<?php

namespace App\Service\EntityLink;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface EntityLinkGeneratorInterface
{
    public static function supportsEntity(): string;

    public function generateLink($entity, UrlGeneratorInterface $urlGenerator): EntityLink;
}
