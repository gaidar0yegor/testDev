<?php

namespace App\Twig;

use App\Service\EntityLink\EntityLinkService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EntityLinkExtension extends AbstractExtension
{
    private EntityLinkService $entityLinkService;

    public function __construct(EntityLinkService $entityLinkService)
    {
        $this->entityLinkService = $entityLinkService;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('entityLink', [$this, 'entityLink'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('entityLink', [$this, 'entityLink'], ['is_safe' => ['html']]),
        ];
    }

    public function entityLink($entityOrClassname, int $id = null): string
    {
        return $this->entityLinkService->generateLink($entityOrClassname, $id);
    }
}
