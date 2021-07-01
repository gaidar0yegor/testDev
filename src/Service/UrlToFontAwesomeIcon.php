<?php

namespace App\Service;

class UrlToFontAwesomeIcon
{
    public const DEFAULT_ICON = 'fa-external-link';

    /**
     * 'trello' means xx.trello.xx will return icon 'fa-trello'
     * 'youtube' => 'youtube-square' means xx.youtube.xx will return icon 'fa-youtube-square'
     */
    public const MAPPER = [
        'github' => 'fa-github',
        'gitlab' => 'fa-gitlab',
        'linkedin' => 'fa-linkedin',
        'trello' => 'fa-trello',
        'twitter' => 'fa-twitter',
        'youtube' => 'fa-youtube',
    ];

    public function getIconForUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $domain = explode('.', $host);
        array_pop($domain);
        $domain = array_pop($domain);

        if (!is_string($domain) || !isset(self::MAPPER[$domain])) {
            return self::DEFAULT_ICON;
        }

        return self::MAPPER[$domain];
    }
}
