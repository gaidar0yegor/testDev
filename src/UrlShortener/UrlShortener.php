<?php

namespace App\UrlShortener;

use App\Entity\ShortUrl;
use App\Repository\ShortUrlRepository;
use App\Service\TokenGenerator;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlShortener
{
    private ShortUrlRepository $shortUrlRepository;

    private TokenGenerator $tokenGenerator;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        ShortUrlRepository $shortUrlRepository,
        TokenGenerator $tokenGenerator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->shortUrlRepository = $shortUrlRepository;
        $this->tokenGenerator = $tokenGenerator;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Creates a new short url from a long url.
     * Don't forget to persist the returned $shortUrl instance.
     */
    public function createShortUrl(string $url, int $tokenSize = 8): ShortUrl
    {
        $shortUrl = $this->shortUrlRepository->findOneAlreadyGenerated($url, $tokenSize);

        if (null !== $shortUrl) {
            return $shortUrl->setReusedAt(new DateTime());
        }

        $shortUrl = new ShortUrl();
        $token = $this->tokenGenerator->generateUrlToken($tokenSize);

        return $shortUrl
            ->setOriginalUrl($url)
            ->setToken($token)
        ;
    }

    /**
     * Get the shareable absolute url from an instance of ShortUrl
     */
    public function generateUrl(ShortUrl $shortUrl): string
    {
        return $this->urlGenerator->generate(
            'app_url_shortener',
            [
                'token' => $shortUrl->getToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
