<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{
    public const ACTIVE_CLASS = 'active';

    private ?Request $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getFilters(): array
    {
        return [
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('activeOn', [$this, 'activeOn']),
        ];
    }

    public function activeOn(string ...$routeNames)
    {
        if (null === $this->request) {
            return '';
        }

        return in_array($this->request->get('_route'), $routeNames)
            ? self::ACTIVE_CLASS
            : ''
        ;
    }
}
