<?php

namespace App\Controller\CorpApp\API\MultiSociete;

use App\Activity\ActivityService;
use App\Entity\DashboardConsolide;
use App\Entity\ProjetActivity;
use App\MultiSociete\UserContext;
use App\Repository\ProjetActivityRepository;
use App\Repository\ProjetRepository;
use App\Security\Role\RoleProjet;
use App\Service\ParticipantService;
use App\Service\StatisticsService;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Voter\HasProductPrivilegeVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/mes-societes/dashboard/consolide")
 */
class DashboardConsolideController extends AbstractController
{
    /**
     * Retourne les derniéres activités par societe
     *
     * @Route(
     *     "/recents-projets/{id}",
     *     defaults={"id"=null},
     *     methods={"GET"},
     *     requirements={"id"="\d+"},
     *     name="api_multisociete_dashboard_consolide_projets_recent"
     * )
     */
    public function getRecentProjets(
        DashboardConsolide $dashboardConsolide = null,
        ProjetActivityRepository $projetActivityRepository,
        ActivityService $activityService,
        UserContext $userContext
    ): JsonResponse {

        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $societeUsers = $dashboardConsolide ? $dashboardConsolide->getSocieteUsers() : $userContext->getUser()->getSocieteUsers();
        $normalizedLastProjetActivities = [];

        foreach ($societeUsers as $societeUser){
            $lastProjetActivities = $projetActivityRepository->findBySocieteUser($societeUser,1);

            foreach ($lastProjetActivities as $key => $projetActivity) {
                if ($projetActivity instanceof ProjetActivity)
                    array_push($normalizedLastProjetActivities,[
                        "id" => $projetActivity->getProjet()->getId(),
                        "societe" => $projetActivity->getProjet()->getSociete()->getRaisonSociale(),
                        "acronyme" => $projetActivity->getProjet()->getAcronyme(),
                        "colorCode" => $projetActivity->getProjet()->getColorCode(),
                        "activity" => $activityService->render($projetActivity->getActivity()),
                        "datetime" => $projetActivity->getActivity()->getDatetime()->format('d/m/Y H:i')
                    ]);
            }
        }

        return new JsonResponse(['recentsProjets' => $normalizedLastProjetActivities]);
    }

    /**
     * Retourne le nombre d'heures passées par projet dans cette année.
     *
     * @Route(
     *     "/heures-par-projet/{year}/{id}",
     *     defaults={"id"=null},
     *     methods={"GET"},
     *     requirements={"year"="\d{4}", "id"="\d+"},
     *     name="api_multisociete_dashboard_consolide_heures_passees_par_projet"
     * )
     */
    public function getHeuresPasseesParProjet(
        int $year,
        DashboardConsolide $dashboardConsolide = null,
        StatisticsService $statisticsService,
        UserContext $userContext
    ): JsonResponse {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $datas = $statisticsService->calculateHeuresMultisocieteParProjetForUser(
            $userContext->getUser(),
            $year,
            $dashboardConsolide
        );

        return new JsonResponse($datas);
    }

    /**
     * Retourne les stats "Nb de projets en cours/terminés"
     * depuis une année N.
     *
     * @Route(
     *      "/projets-statuts/since-{sinceYear}/{id}",
     *     defaults={"id"=null},
     *      methods={"GET"},
     *     requirements={"sinceYear"="\d{4}", "id"="\d+"},
     *      name="api_multisociete_dashboard_consolide_projets_statuts"
     * )
     */
    public function getProjetsStatuts(
        int $sinceYear,
        DashboardConsolide $dashboardConsolide = null,
        ProjetRepository $projetRepository,
        UserContext $userContext
    ): JsonResponse {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $societeUsers = $dashboardConsolide ? $dashboardConsolide->getSocieteUsers() : $userContext->getUser()->getSocieteUsers();

        $now = new \DateTime();

        $stats = [
            'active' => 0,
            'finished' => 0,
            'suspended' => 0,
        ];

        foreach ($societeUsers as $societeUser){
            $projets = $societeUser->isAdminFo()
                ? $projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $sinceYear)
                : $projetRepository->findAllForUserSinceYear($societeUser, RoleProjet::OBSERVATEUR, $sinceYear)
            ;

            foreach ($projets as $projet) {
                if ($projet->getIsSuspended()) {
                    ++$stats['suspended'];
                } elseif (null === $projet->getDateFin() || $projet->getDateFin() >= $now) {
                    ++$stats['active'];
                } else {
                    ++$stats['finished'];
                }
            }
        }

        return new JsonResponse($stats);
    }

    /**
     * Retourne le nombre de projet RDI/non-RDI par année
     * depuis une année N.
     *
     * @return JsonResponse
     *
     * @Route(
     *     "/projets-type/since-{sinceYear}/{id}",
     *     defaults={"id"=null},
     *     methods={"GET"},
     *     requirements={"sinceYear"="\d{4}", "id"="\d+"},
     *     name="api_multisociete_dashboard_consolide_projets_type"
     * )
     */
    public function getProjetsTypesSinceYear(
        int $sinceYear,
        DashboardConsolide $dashboardConsolide = null,
        ProjetRepository $projetRepository,
        UserContext $userContext
    ): JsonResponse {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $societeUsers = $dashboardConsolide ? $dashboardConsolide->getSocieteUsers() : $userContext->getUser()->getSocieteUsers();

        $projets = [];
        foreach ($societeUsers as $societeUser){
            $projets = array_merge(
                $projets,
                $societeUser->isAdminFo()
                ? $projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $sinceYear)
                : $projetRepository->findAllForUserSinceYear($societeUser, RoleProjet::OBSERVATEUR, $sinceYear)
            );
        }

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

    /**
     * Retourne les stats "Moi" vs "Equipe"
     *
     * @Route(
     *     "/moi-vs-equipe/{year}/{id}",
     *     defaults={"id"=null},
     *     methods={"GET"},
     *     requirements={"year"="\d{4}", "id"="\d+"},
     *     name="api_multisociete_dashboard_consolide_moi_vs_equipe"
     * )
     */
    public function getMoiVsEquipe(
        int $year,
        DashboardConsolide $dashboardConsolide = null,
        ProjetRepository $projetRepository,
        StatisticsService $statisticsService,
        UserContext $userContext,
        ParticipantService $participantService
    ): JsonResponse {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $societeUsers = $dashboardConsolide ? $dashboardConsolide->getSocieteUsers() : $userContext->getUser()->getSocieteUsers();

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
                'moi' => 0,
            ],
        ];

        foreach ($societeUsers as $societeUser){
            $projets = $projetRepository->findAllForUserInYear(
                $societeUser,
                $societeUser->isAdminFo()
                    ? null
                    : RoleProjet::OBSERVATEUR
                ,
                $year
            );

            $heuresParProjet = $statisticsService->calculateHeuresParProjet($societeUser->getSociete(), $year);
            $monthsValidForYear = $statisticsService->calculateMonthsValidByYear($societeUser, $year);

            $stats['moisValides']['moi'] += $monthsValidForYear;

            foreach ($projets as $projet) {
                $userIsContributing = $participantService->hasRoleOnProjet(
                    $societeUser,
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

            $stats['tempsTotal']['moi'] += $statisticsService->calculateHeuresForUser(
                $societeUser,
                $year
            );
        }

        return new JsonResponse($stats);
    }
}
