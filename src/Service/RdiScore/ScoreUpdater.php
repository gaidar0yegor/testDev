<?php

namespace App\Service\RdiScore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Le score RDI d'un projet est un coefficient entre 0 et 1.
 * En théorie il tend vers 1 quand un projet tend à être elligible RDI,
 * et donc potentiellement pourra recevoir des aides style CIR/CII...
 *
 * Ce service propose une première version d'un algorithme naif
 * qui se base sur l'apparence de mots clés liés à la recherche et innovation
 * dans le contenu texte du projet et de ses faits marquants
 * pour calculer le score RDI.
 *
 * Pour cela, il indexe les projets dans un index Elasic Search,
 * puis effectue une recherche par mots clés dans les textes du projet,
 * et se sert du score attribué aux projets pour définir un coefficient.
 *
 * ----------------- NOT USED ----------------------
 */
class ScoreUpdater
{
    private const COEF_PROJET_TITRE = 5;
    private const COEF_PROJET_RESUME = 4;
    private const COEF_FAIT_MARQUANT_TITRE = 3;
    private const COEF_FAIT_MARQUANT_DESC = 2;

    private Connection $db;

    private Client $elastic;

    private string $projetIndex;

    private LoggerInterface $logger;

    private Statement $updateScoreStatement;

    public function __construct(
        Connection $db,
        Client $elastic,
        string $projetIndex
    ) {
        $this->db = $db;
        $this->elastic = $elastic;
        $this->projetIndex = $projetIndex;

        $this->logger = new NullLogger();

        $this->updateScoreStatement = $this->db->prepare('
            update projet
            set
                rdi_score = :score,
                rdi_score_reliability = :reliability
            where id = :projetId
        ');
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Update all projets RDI score in database, using the elastic search instance.
     */
    public function updateAllProjetScore(): void
    {
        $this->logger->info('Recalculating projets RDI score...');

        $this->db->beginTransaction();

        $projetStmt = $this->db->prepare('
            select id, acronyme, titre, resume
            from projet
        ');

        $projetStmt->execute();

        $this->resetIndex();

        while (($projet = $projetStmt->fetchAssociative()) !== false) {
            $this->logger->info('Indexing projet {projet}', ['projet' => $projet['acronyme']]);

            $this->indexProjet($projet);
        }

        $this->logger->info('Waiting for elasticsearch to index all projets...');
        $this->waitIndexation();
        $this->logger->info('Indexation finished.');

        $response = $this->runKeywordsSearch();

        $this->db->executeQuery('
            update projet
            set
                rdi_score = 0,
                rdi_score_reliability = 0
        ');

        foreach ($response['hits']['hits'] as $hit) {
            $this->logger->info('Updating projet {projet}', ['projet' => $hit['_source']['acronyme']]);

            $this->updateProjetFromHit($hit);
        }

        $this->db->commit();

        $this->logger->info('Database commited.');
    }

    /**
     * Reset projet index to make score deterministic
     */
    private function resetIndex(): void
    {
        $this->elastic->indices()->delete(['index' => $this->projetIndex, 'ignore_unavailable' => true]);
        $this->elastic->indices()->create(['index' => $this->projetIndex]);
    }

    /**
     * Put a projet in elastic search from database fetch.
     *
     * @param $projet
     */
    private function indexProjet(array $projet): void
    {
        $volume = 0;

        $volume += self::countStringWords($projet['titre']) * self::COEF_PROJET_TITRE;
        $volume += self::countStringWords($projet['resume']) * self::COEF_PROJET_RESUME;

        $faitMarquantStmt = $this->db->prepare('
            select titre, description
            from fait_marquant
            where projet_id = ?
        ');

        $faitMarquantStmt->execute([$projet['id']]);

        while (($faitMarquant = $faitMarquantStmt->fetchAssociative()) !== false) {
            $projet['faitMarquants'][] = $faitMarquant;

            $volume += self::countStringWords($faitMarquant['titre']) * self::COEF_FAIT_MARQUANT_TITRE;
            $volume += self::countStringWords($faitMarquant['description']) * self::COEF_FAIT_MARQUANT_DESC;
        }

        $projet['volume'] = $volume;

        $params = [
            'index' => $this->projetIndex,
            'id'    => $projet['id'],
            'body'  => $projet,
        ];

        $this->elastic->index($params);
    }

    /**
     * Uses RDI keywords to run a search on multiple projet field
     * to get relevancy score for every projet.
     */
    private function runKeywordsSearch(): array
    {
        $rdiSearch = [
            'index' => $this->projetIndex,
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'multi_match' => [
                                'query' => join(' ', RdiKeywords::LIST),
                                'fields' => [
                                    'titre^'.self::COEF_PROJET_TITRE,
                                    'resume^'.self::COEF_PROJET_RESUME,
                                    'faitMarquants.titre^'.self::COEF_FAIT_MARQUANT_TITRE,
                                    'faitMarquants.description^'.self::COEF_FAIT_MARQUANT_DESC,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->elastic->search($rdiSearch);
    }

    /**
     * Calculate the final RDI score and reliability from an elastic search hit and score.
     */
    private function updateProjetFromHit(array $hit): void
    {
        $projet = $hit['_source'];

        // Amount of text data ponderated by titre, description...
        $volume = $projet['volume'];

        // Elastic search relevance divided by text
        $ratio = $hit['_score'] / $volume;

        // Score caped between 0 and 1 using sigmoid function
        $rdiScore = 2 / (1 + exp(-80 * $ratio)) - 1;

        // Reliability caped between 0 and 1 using exp function
        $reliability = 1 - exp(-$volume / 2000);

        $this->dbUpdateProjetScore($projet['id'], $rdiScore, $reliability);
    }

    /**
     * Uses prepared query to update a single projet score.
     */
    private function dbUpdateProjetScore(int $projetId, float $rdiScore, float $reliability): void
    {
        $this->updateScoreStatement->execute([
            'projetId' => $projetId,
            'score' => $rdiScore,
            'reliability' => $reliability,
        ]);
    }

    /**
     * Wait elastic search to index all last documents
     * so that search result take account of all projets.
     */
    private function waitIndexation(): void
    {
        $this->elastic->indices()->refresh(['index' => $this->projetIndex]);
    }

    /**
     * Count the words in a given string.
     * Returns 0 if null.
     */
    private static function countStringWords(?string $s): int
    {
        if (null === $s) {
            return 0;
        }

        return preg_match_all('/\pL+/u', $s, $matches);
    }
}
