<?php

namespace App\Service\RdiScore;

use App\Entity\Projet;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Le score RDI d'un projet est un coefficient entre 0 et 1.
 * En théorie il tend vers 1 quand un projet tend à être elligible RDI,
 * et donc potentiellement pourra recevoir des aides style CIR/CII...
 */
class ProjetScoreRdiUpdater
{
    private const COEF_GLOBAL = 0.4;
    private const COEF_GLOBAL_TEXT = 0.5;
    private const COEF_GLOBAL_PPP = 0.35;
    private const COEF_GLOBAL_COLLABORATIF = 0.15;

    private const COEF_FAIT_MARQUANT = 0.6;
    private const COEF_FAIT_MARQUANT_TEXT = 0.8;
    private const COEF_FAIT_MARQUANT_MEDIA = 0.2;

    private const COEF_KEYWORD_1 = 1;
    private const COEF_KEYWORD_2 = 0.5;
    private const COEF_EQUATION = 1.23;
    private const COEF_FILE = 0.15;
    private const COEF_IMAGE = 0.15;

    private const SCORE_MAX = 0.95;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function updateProjetsScore(LoggerInterface $logger = null): void
    {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $projets = $this->em->getRepository(Projet::class)->findAll();

        $logger->info("Get " . count($projets) . " Projects");

        foreach ($projets as $projet) {
            if ($projet->getRdiDomains()->count() > 0) {
                $levelKeywords = [
                    'keywords_1' => [],
                    'keywords_2' => RdiKeywordsStatic::getKeywords()
                ];

                foreach ($projet->getRdiDomains() as $rdiDomain) {
                    if (count($rdiDomain->getKeywords()) > 0) {
                        $levelKeywords['keywords_1'] = array_merge($levelKeywords['keywords_1'], $rdiDomain->getKeywords());
                    }
                }

                $projetData = [];
                $projetData['id'] = $projet->getId();
                $projetData['projet_collaboratif'] = (int)$projet->getProjetCollaboratif();
                $projetData['projet_ppp'] = (int)$projet->getProjetPpp();
                $projetData['resume'] = self::convertToPlainText($projet->getTitre() . ' ' . $projet->getResume());

                foreach ($levelKeywords as $level => $keywords) {
                    $projetData['nbrKeywordsGlobal'][$level] = self::countOccurrenceArrayOfWordsInText($keywords,$projetData['resume']);
                }

                $params = [];
                $firstYear = $projet->getDateDebut() ? (int)$projet->getDateDebut()->format('Y') : (int)(new \DateTime())->format('Y');
                $lastYear = $projet->getDateFin() && $projet->getDateFin() < (new \DateTime()) ? (int)$projet->getDateFin()->format('Y') : (int)(new \DateTime())->format('Y');
                for ($i = $firstYear; $i <= $lastYear; $i++) {
                    $params[$i] = [
                        'nbrFaitMarquants' => 0,
                        'faitMarquantsTexts' => '',
                        'nbrKeywordsFms' => ['keywords_1' => 0, 'keywords_2' => 0],
                        'nbrFiles' => 0,
                        'nbrEquation' => 0,
                        'nbrImage' => 0,
                        'volumeFms' => 0,
                    ];
                }

                foreach ($projet->getFichierProjets() as $fichierProjet) {
                    if ($firstYear <= (int)$fichierProjet->getFichier()->getDateUpload()->format('Y') && $lastYear >= (int)$fichierProjet->getFichier()->getDateUpload()->format('Y')){
                        $params[$fichierProjet->getFichier()->getDateUpload()->format('Y')]['nbrFiles'] += 1;
                    }
                }

                foreach ($projet->getFaitMarquants() as $faitMarquant) {
                    if ($faitMarquant->getTrashedAt() === null){
                        $year = $faitMarquant->getDate()->format('Y');
                        $text = self::convertToPlainText($faitMarquant->getTitre() . ' ' . $faitMarquant->getDescription());
                        $params[$year]['faitMarquantsTexts'] .= $text;
                        $params[$year]['nbrFaitMarquants'] += 1;
                        $params[$year]['volumeFms'] += self::countStringWords($text);
                        $params[$year]['nbrEquation'] += substr_count($faitMarquant->getTitre() . ' ' . $faitMarquant->getDescription(), '</math>');
                        $params[$year]['nbrImage'] += substr_count($faitMarquant->getTitre() . ' ' . $faitMarquant->getDescription(), '<img ');
                    }
                }

                $projetData['volumeMoyenAllFms'] = 0;
                foreach ($params as $year => $param) {
                    foreach ($levelKeywords as $level => $keywords) {
                        $params[$year]['nbrKeywordsFms'][$level] += self::countOccurrenceArrayOfWordsInText($keywords,$param['faitMarquantsTexts']);
                    }
                    $param['volumeMoyenFms'] = $param['nbrFaitMarquants'] > 0 ? floor($param['volumeFms'] / $param['nbrFaitMarquants']) : 0;
                    $projetData['volumeMoyenAllFms'] += $param['volumeMoyenFms'];
                }

                $projetData['params'] = $params;

                $globalScore = $this->calculGlobalProjetScore($projetData);
                $annualScores = $this->calculAnnualFmsScores($projetData);

                $this->dbUpdateProjetScore($projet, $globalScore, $annualScores);

                $logger->info("Update Rdi score for project : " . $projet->getAcronyme());
            }
        }

        $this->em->flush();
    }

    private function calculGlobalProjetScore(array $projetData): float
    {
        $seuilFmRDI = $projetData['volumeMoyenAllFms'] > 0 ? 5 / $projetData['volumeMoyenAllFms'] : 0;

        $scoreKeywordsGlobal = $projetData['volumeMoyenAllFms'] > 0 ? ($projetData['nbrKeywordsGlobal']['keywords_1'] * self::COEF_KEYWORD_1 + $projetData['nbrKeywordsGlobal']['keywords_2'] * self::COEF_KEYWORD_2) / $projetData['volumeMoyenAllFms'] : 0;

        $scoreKeywordsGlobal = $scoreKeywordsGlobal < $seuilFmRDI ? self::COEF_GLOBAL_TEXT * ($scoreKeywordsGlobal / $seuilFmRDI) : self::COEF_GLOBAL_TEXT;

        return ($projetData['projet_collaboratif'] * self::COEF_GLOBAL_COLLABORATIF + $projetData['projet_ppp'] * self::COEF_GLOBAL_PPP + $scoreKeywordsGlobal) * self::COEF_GLOBAL;
    }

    private function calculAnnualFmsScores(array $projetData): array
    {
        $annualScores = [];

        foreach ($projetData['params'] as $year => $param) {

            if ($param['volumeFms'] === 0){
                $annualScores[$year] = 0;
            } else {
                $seuilFmRDI = $param['volumeFms'] * 5 / 250;
                $totalKeyword = array_sum($param['nbrKeywordsFms']);

                $scoreKeywordsFms = $totalKeyword > $seuilFmRDI ? self::COEF_FAIT_MARQUANT_TEXT : ($totalKeyword / $seuilFmRDI) * self::COEF_FAIT_MARQUANT_TEXT;


                $totalMedia = $param['nbrEquation'] * self::COEF_EQUATION + $param['nbrFiles'] * self::COEF_FILE + $param['nbrImage'] * self::COEF_IMAGE;
                $scoreMedia = $totalMedia > ($param['nbrFaitMarquants'] * self::COEF_EQUATION) ? self::COEF_FAIT_MARQUANT_MEDIA : ($totalMedia / ($param['nbrFaitMarquants'] * self::COEF_EQUATION)) * self::COEF_FAIT_MARQUANT_MEDIA ;


                $annualScores[$year] = ($scoreKeywordsFms + $scoreMedia) * self::COEF_FAIT_MARQUANT;
            }
        }

        return $annualScores;
    }

    private function dbUpdateProjetScore(Projet $projet, float $globalScore, array $annualScores): void
    {
        foreach ($annualScores as $year => $annualScore)
        {
            $score = $globalScore + $annualScore;
            if (!$projet->getProjetPpp()){
                $score = $score * ($projet->getProjetCollaboratif() ? 1.14 : 1.2);
            }
            $annualScores[$year] = round($score > self::SCORE_MAX ? self::SCORE_MAX : $score,2);
        }

        $projet->setAnnualRdiScores($annualScores);

        $this->em->persist($projet);
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

    /**
     * Convert Html text to plain text
     */
    private static function convertToPlainText(string $s): string
    {
        $s = htmlspecialchars(trim(strip_tags(html_entity_decode($s, ENT_QUOTES, 'UTF-8'))));

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[.,:;]/', '', $s)));
    }

    /**
     * Convert Html text to plain text
     */
    private static function countOccurrenceArrayOfWordsInText(array $words, string $text): int
    {
        $somme = 0;
        $arrayText = explode(' ', strtoupper($text));
        foreach ($words as $word){
            $somme += count(array_keys($arrayText, strtoupper($word)));
        }

        return $somme;

//        $somme = 0;
//        $occurentWords = 0;
//        foreach ($words as $word){
//            $nbrOccurence = substr_count($text, strtoupper($word));
//            $somme += $nbrOccurence;
//            $occurentWords += (int)$nbrOccurence > 0;
//        }
//
//        return $somme * $occurentWords;
    }
}
