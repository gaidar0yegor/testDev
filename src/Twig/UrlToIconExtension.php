<?php

namespace App\Twig;

use App\Service\UrlToFontAwesomeIcon;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UrlToIconExtension extends AbstractExtension
{
    private UrlToFontAwesomeIcon $urlToFontAwesomeIcon;

    public function __construct(UrlToFontAwesomeIcon $urlToFontAwesomeIcon)
    {
        $this->urlToFontAwesomeIcon = $urlToFontAwesomeIcon;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('faUrlIcon', [$this, 'faUrlIcon']),
        ];
    }

    public function faUrlIcon(?string $url): string
    {
        if (null === $url) {
            return UrlToFontAwesomeIcon::DEFAULT_ICON;
        }

        return $this->urlToFontAwesomeIcon->getIconForUrl($url);
    }
}
