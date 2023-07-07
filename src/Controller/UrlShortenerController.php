<?php

namespace App\Controller;

use App\Entity\ShortUrl;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlShortenerController extends AbstractController
{
    /**
     * @Route("/l/{token}", name="app_url_shortener")
     */
    public function index(
        ShortUrl $shortUrl,
        EntityManagerInterface $em
    ): Response {
        $shortUrl->click();

        $em->flush();

        return $this->redirect($shortUrl->getOriginalUrl());
    }
}
