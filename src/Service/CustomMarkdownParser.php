<?php

namespace App\Service;

use Knp\Bundle\MarkdownBundle\Parser\Preset\Flavored;

class CustomMarkdownParser extends Flavored
{
    /**
     * {@inheritDoc}
     */
    public function transformMarkdown($text)
    {
        if (!is_string($text)) {
            return '';
        }

        $text = self::addMissingChevronsToLinks($text);

        return parent::transformMarkdown($text);
    }

    /**
     * Before transform to markdown, adds < and > to automatically create clickable links,
     * even if url has no < and >.
     */
    private static function addMissingChevronsToLinks(string $text): string
    {
        $urlRegex = '%(^| )([a-z]+:\/\/[^ \t\r\n\)]+)%m';

        return preg_replace($urlRegex, '$1<$2>', $text);
    }
}
