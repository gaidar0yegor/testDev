<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TruncateTextExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate', [$this, 'truncate']),
        ];
    }
    public function truncate(string $text, int $length)
    {
        $subText = substr($text, 0, $length);
        $lastSpace = strrpos($subText, ' ');

        return ( $lastSpace !== false ? substr($subText, 0, $lastSpace) : $subText ). '...';
    }
}
