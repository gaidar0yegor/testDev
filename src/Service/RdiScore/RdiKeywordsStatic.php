<?php

namespace App\Service\RdiScore;

use App\Entity\RdiDomain;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * la liste statique des keywords de niveau 2
 */
class RdiKeywordsStatic
{
    public static function getKeywords(): array
    {
        return [
            'innovation',
            'nouveau', 
            'produit', 
            'product', 
            'conception', 
            'ecoconception', 
            'design', 
            'test', 
            'essais', 
            'trials', 
            'prototype', 
            'poc',
            'ergonomie',
            'production',
            'développement',
            'development',
            'intégration',
            'integration'
        ];
    }
}
