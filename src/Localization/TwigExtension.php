<?php

namespace App\Localization;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Create twig filter to ransform "en" to "English", "fr" to "Français", ...
 */
class TwigExtension extends AbstractExtension
{
    private array $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('localeCodeToText', [$this, 'localeCodeToText']),
        ];
    }

    public function localeCodeToText($localeCode): string
    {
        if (!array_key_exists($localeCode, $this->locales)) {
            return $localeCode;
        }

        return $this->locales[$localeCode];
    }
}
