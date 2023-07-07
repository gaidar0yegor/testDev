<?php

namespace App\Service\RdiScore;

use App\Entity\RdiDomain;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * générer les keywords depuis l'api de https://api.archives-ouvertes.fr
 */
class RdiKeywordsGeneratorFromAPI
{
    private HttpClientInterface $client;
    private string $apiUrl;

    public function __construct(HttpClientInterface $client, string $apiUrl)
    {
        $this->client = $client;
        $this->apiUrl = $apiUrl;
    }

    public function getKeywords(RdiDomain $rdiDomain) : array
    {
        $apiUrl = str_replace('{rdiDomain_level}',$rdiDomain->getLevel(),str_replace('{rdiDomain_cle}',$rdiDomain->getCle(),$this->apiUrl));
        $response = $this->client->request('GET', $apiUrl);

        $response = json_decode($response->getContent(),true);

        if (!isset($response['facet_counts'])){
            return [];
        }

        $results = $response['facet_counts']['facet_fields']['keyword_s'];

        $keywords = [];
        $sumOccurences = 0;
        for ($key = 0; $key < count($results) - 1; $key += 2){
            if ($results[$key] === "" || $results[$key][0] === "!" || $results[$key][0] === "$") continue;
            $word = mb_strtolower($results[$key], 'UTF-8');
            $sumOccurences += $results[$key + 1];
            if (key_exists($word, $keywords)){
                $keywords[$word] += $results[$key + 1];
            } else {
                $keywords[$word] = $results[$key + 1];
            }
        }

        return array_keys($keywords);

        $cumul = 0;
        $keywords1 = [];
        $keywords2 = [];
        $keywords3 = [];
        foreach ($keywords as $keyword => $occurence){
            $cumul += $occurence;
            if (($cumul / $sumOccurences) <= 0.3){
                $keywords1[] = $keyword;
            } elseif (($cumul / $sumOccurences) > 0.3 && ($cumul / $sumOccurences) <= 0.8){
                $keywords2[] = $keyword;
            } else {
                $keywords3[] = $keyword;
            }
        }

        return [
            'keywords_1' => $keywords1,
            'keywords_2' => $keywords2,
            'keywords_3' => $keywords3,
        ];
    }
}
