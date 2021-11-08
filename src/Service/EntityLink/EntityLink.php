<?php

namespace App\Service\EntityLink;

class EntityLink
{
    private $text;

    private $url;

    public function __construct(string $text, string $url)
    {
        $this->text = $text;
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function __toString()
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $this->url,
            $this->text
        );
    }
}
