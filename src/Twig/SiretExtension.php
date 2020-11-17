<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SiretExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('formatSiret', [$this, 'formatSiret']),
        ];
    }

    public function getFunctions(): array
    {
        return [
        ];
    }

    public function formatSiret($value)
    {
        $value = str_replace(' ', '', $value);

        preg_match('/(.{1,3})?(.{1,3})?(.{1,3})?(.{1,5})?(.+)?/', $value, $matches);

        array_shift($matches);

        return join(' ', $matches);
    }
}
