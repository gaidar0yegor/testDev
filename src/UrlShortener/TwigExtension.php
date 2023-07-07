<?php

namespace App\UrlShortener;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    private UrlShortener $urlShortener;

    private EntityManagerInterface $em;

    public function __construct(
        UrlShortener $urlShortener,
        EntityManagerInterface $em
    ) {
        $this->urlShortener = $urlShortener;
        $this->em = $em;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('shortenUrl', [$this, 'shortenUrl']),
        ];
    }

    public function shortenUrl(string $url, int $tokenSize = 8): string
    {
        $shortUrl = $this->urlShortener->createShortUrl($url, $tokenSize);

        $this->em->persist($shortUrl);
        $this->em->flush();

        return $this->urlShortener->generateUrl($shortUrl);
    }
}
