<?php

namespace App\Controller\API;

use App\Activity\ActivityService;
use App\Entity\ProjetActivity;
use App\Repository\ProjetActivityRepository;
use App\Repository\ProjetRepository;
use App\Security\Role\RoleProjet;
use App\Service\CraService;
use App\Service\DateMonthService;
use App\Service\ParticipantService;
use App\Service\StatisticsService;
use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/api/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * Retourne si l'utilisateur est à jour dans la saisie de ses temps.
     *
     * @Route(
     *      "/temps-du-mois/{month}",
     *      methods={"GET"},
     *      requirements={"month"="\d{4}-\d{2}"},
     *      name="api_dashboard_temps_du_mois"
     * )
     */
    public function getTempsDuMois(
        string $month = null,
        CraService $craService,
        UserContext $userContext,
        DateMonthService $dateMonthService
    ) {
        $cra = $craService->loadCraForUser(
            $userContext->getSocieteUser(),
            $dateMonthService->getCurrentMonth(null === $month ? 'now' : $month)
        );

        $notValidMois = $craService->getFirstNotValidMonth($userContext->getSocieteUser());

        return new JsonResponse([
            'month' => $cra->getMois()->format('Y-m'),
            'isCraSubmitted' => $cra->isCraSubmitted(),
            'isTempsPassesSubmitted' => $cra->isTempsPassesSubmitted(),
            'hasTempsPasses' => $cra->hasTempsPasses(),
            'craModifiedAt' => null === $cra->getCraModifiedAt()
                ? null
                : $cra->getCraModifiedAt()->format('d M Y')
            ,
            'tempsPassesModifiedAt' => null === $cra->getTempsPassesModifiedAt()
                ? null
                : $cra->getTempsPassesModifiedAt()->format('d M Y')
            ,
            'notValidMois' => $notValidMois
        ]);
    }

    /**
     * Retourne les derniers projets accédés.
     *
     * @Route(
     *      "/recents-projets",
     *      methods={"GET"},
     *      name="api_dashboard_projets_recents"
     * )
     */
    public function getRecentsProjets(
        UserContext $userContext,
        ProjetActivityRepository $projetActivityRepository,
        ActivityService $activityService,
        NormalizerInterface $normalizer
    ): JsonResponse {
        $lastProjetActivities = $projetActivityRepository->findBySocieteUser($userContext->getSocieteUser());
        $normalizedLastProjetActivities = [];

        foreach ($lastProjetActivities as $key => $projetActivity) {
            if ($projetActivity instanceof ProjetActivity)
            array_push($normalizedLastProjetActivities,[
                "id" => $projetActivity->getProjet()->getId(),
                "acronyme" => $projetActivity->getProjet()->getAcronyme(),
                "colorCode" => $projetActivity->getProjet()->getColorCode(),
                "activity" => $activityService->render($projetActivity->getActivity()),
                "datetime" => $projetActivity->getActivity()->getDatetime()->format('d/m/Y H:i'),
            ]);
        }

        return new JsonResponse(['recentsProjets' => $normalizedLastProjetActivities]);
    }

    /**
     * Retourne le nombre d'heures passées par projet dans cette année.
     *
     * @Route(
     *      "/heures-par-projet/{year}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}"},
     *      name="api_dashboard_heures_passees_par_projet"
     * )
     */
    public function getHeuresPasseesParProjet(
        int $year,
        StatisticsService $statisticsService,
        UserContext $userContext
    ) {
        $datas = $statisticsService->calculateHeuresParProjetForUser(
            $userContext->getSocieteUser(),
            $year
        );
//dump($datas);exit;
        return new JsonResponse($datas);
    }

    /**
     * Retourne les stats "Moi" vs "Equipe"
     *
     * @Route(
     *      "/moi-vs-equipe/{year}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}"},
     *      name="api_dashboard_moi_vs_equipe"
     * )
     */
    public function getMoiVsEquipe(
        int $year,
        ProjetRepository $projetRepository,
        StatisticsService $statisticsService,
        UserContext $userContext,
        ParticipantService $participantService
    ) {
        $projets = $projetRepository->findAllForUserInYear(
            $userContext->getSocieteUser(),
            $userContext->getSocieteUser()->isAdminFo()
                ? null
                : RoleProjet::OBSERVATEUR
            ,
            $year
        );
        $heuresParProjet = $statisticsService->calculateHeuresParProjet($userContext->getSocieteUser()->getSociete(), $year);
        $monthsValidForYear = $statisticsService->calculateMonthsValidByYear($userContext->getSocieteUser(), $year);

        $stats = [
            'projets' => [
                'moi' => 0,
                'equipe' => 0,
            ],
            'projetsRdi' => [
                'moi' => 0,
                'equipe' => 0,
            ],
            'tempsTotal' => [
                'moi' => 0,
                'equipe' => 0,
            ],
            'moisValides' => [
                'moi' => $monthsValidForYear,
            ],
        ];

        foreach ($projets as $projet) {
            $userIsContributing = $participantService->hasRoleOnProjet(
                $userContext->getSocieteUser(),
                $projet,
                RoleProjet::CONTRIBUTEUR
            );

            $stats['projets']['equipe']++;
            $stats['tempsTotal']['equipe'] += $heuresParProjet[$projet->getAcronyme()] ?? 0.0;

            if ($userIsContributing) {
                $stats['projets']['moi']++;
            }

            if ($projet->isRdi()) {
                $stats['projetsRdi']['equipe']++;

                if ($userIsContributing) {
                    $stats['projetsRdi']['moi']++;
                }
            }
        }

        $stats['tempsTotal']['moi'] = $statisticsService->calculateHeuresForUser(
            $userContext->getSocieteUser(),
            $year
        );

        return new JsonResponse($stats);
    }

    /**
     * Retourne les stats "Nb de projets en cours/terminés"
     * depuis une année N.
     *
     * @Route(
     *      "/projets-statuts/since-{sinceYear}",
     *      methods={"GET"},
     *      requirements={"sinceYear"="\d{4}"},
     *      name="api_dashboard_projets_statuts"
     * )
     */
    public function getProjetsStatuts(
        int $sinceYear,
        UserContext $userContext,
        ProjetRepository $projetRepository
    ) {
        $now = new \DateTime();
        $projets = $userContext->getSocieteUser()->isAdminFo()
            ? $projetRepository->findAllProjectsPerSociete($userContext->getSocieteUser()->getSociete(), $sinceYear)
            : $projetRepository->findAllForUserSinceYear($userContext->getSocieteUser(), RoleProjet::OBSERVATEUR, $sinceYear)
        ;

        $stats = [
            'active' => 0,
            'finished' => 0,
            'suspended' => 0,
        ];

        foreach ($projets as $projet) {
            if ($projet->getIsSuspended()) {
                ++$stats['suspended'];
            } elseif (null === $projet->getDateFin() || $projet->getDateFin() >= $now) {
                ++$stats['active'];
            } else {
                ++$stats['finished'];
            }
        }

        return new JsonResponse($stats);
    }

    /**
     * Retourne le nombre de projet RDI/non-RDI par année
     * depuis une année N.
     *
     * @return JsonResponse Sous la forme :
     *      [
     *          2015 => [
     *              'projets' => 5,
     *              'projetsRdi' => 2,
     *          ],
     *          2016 => [
     *              'projets' => 7,
     *              'projetsRdi' => 3,
     *          ],
     *          ...
     *      ];
     *
     * @Route(
     *      "/projets-type/since-{sinceYear}",
     *      methods={"GET"},
     *      requirements={"sinceYear"="\d{4}"},
     *      name="api_dashboard_projets_type"
     * )
     */
    public function getProjetsTypesSinceYear(
        int $sinceYear,
        UserContext $userContext,
        ProjetRepository $projetRepository
    ) {
        $projets = $userContext->getSocieteUser()->isAdminFo()
            ? $projetRepository->findAllProjectsPerSociete($userContext->getSocieteUser()->getSociete(), $sinceYear)
            : $projetRepository->findAllForUserSinceYear($userContext->getSocieteUser(), RoleProjet::OBSERVATEUR, $sinceYear)
        ;

        $currentYear = intval((new \DateTime())->format('Y'));

        $stats = [];

        for ($i = $sinceYear; $i <= $currentYear; ++$i) {
            $stats[$i] = [
                'projets' => 0,
                'projetsRdi' => 0,
            ];
        }

        foreach ($projets as $projet) {
            $isRdi = $projet->isRdi();
            $projetYearStart = null === $projet->getDateDebut()
                ? $sinceYear
                : intval($projet->getDateDebut()->format('Y'))
            ;
            $projetYearEnd = null === $projet->getDateFin()
                ? $currentYear
                : intval($projet->getDateFin()->format('Y'))
            ;

            $from = max($projetYearStart, $sinceYear);
            $to = min($projetYearEnd, $currentYear);

            for ($i = $from; $i <= $to; ++$i) {
                ++$stats[$i]['projets'];

                if ($isRdi) {
                    ++$stats[$i]['projetsRdi'];
                }
            }
        }

        return new JsonResponse($stats);
    }
}
