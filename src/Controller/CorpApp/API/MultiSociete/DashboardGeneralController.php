<?php

namespace App\Controller\CorpApp\API\MultiSociete;

use App\Activity\ActivityService;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use App\Repository\ProjetActivityRepository;
use App\Repository\ProjetRepository;
use App\Security\Role\RoleProjet;
use App\Service\StatisticsService;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Voter\HasProductPrivilegeVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/mes-societes/dashboard/general")
 */
class DashboardGeneralController extends AbstractController
{
    /**
     * Retourne les derniéres activités par societe
     *
     * @Route(
     *      "/recents-projets/{id}",
     *      methods={"GET"},
     *      name="api_multisociete_dashboard_general_projets_recent"
     * )
     */
    public function getRecentProjets(
        SocieteUser $societeUser,
        ProjetActivityRepository $projetActivityRepository,
        ActivityService $activityService
    ): JsonResponse {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $lastProjetActivities = $projetActivityRepository->findBySocieteUser($societeUser, 10);
        $normalizedLastProjetActivities = [];

        foreach ($lastProjetActivities as $key => $projetActivity) {
            if ($projetActivity instanceof ProjetActivity)
            array_push($normalizedLastProjetActivities,[
                "id" => $projetActivity->getProjet()->getId(),
                "acronyme" => $projetActivity->getProjet()->getAcronyme(),
                "colorCode" => $projetActivity->getProjet()->getColorCode(),
                "activity" => $activityService->render($projetActivity->getActivity()),
                "datetime" => $projetActivity->getActivity()->getDatetime()->format('d/m/Y H:i')
            ]);
        }

        return new JsonResponse(['recentsProjets' => $normalizedLastProjetActivities]);
    }

    /**
     * Retourne le nombre d'heures passées par projet dans cette année.
     *
     * @Route(
     *      "/heures-par-projet/{id}/{year}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}"},
     *      name="api_multisociete_dashboard_general_heures_passees_par_projet"
     * )
     */
    public function getHeuresPasseesParProjet(
        SocieteUser $societeUser,
        int $year,
        StatisticsService $statisticsService
    ) {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $datas = $statisticsService->calculateHeuresParProjetForUser(
            $societeUser,
            $year
        );

        return new JsonResponse($datas);
    }

    /**
     * Retourne les stats "Nb de projets en cours/terminés"
     * depuis une année N.
     *
     * @Route(
     *      "/projets-statuts/{id}/since-{sinceYear}",
     *      methods={"GET"},
     *      requirements={"sinceYear"="\d{4}"},
     *      name="api_multisociete_dashboard_general_projets_statuts"
     * )
     */
    public function getProjetsStatuts(
        SocieteUser $societeUser,
        int $sinceYear,
        ProjetRepository $projetRepository
    ) {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $now = new \DateTime();
        $projets = $societeUser->isAdminFo()
            ? $projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $sinceYear)
            : $projetRepository->findAllForUserSinceYear($societeUser, RoleProjet::OBSERVATEUR, $sinceYear)
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
     * @return JsonResponse
     *
     * @Route(
     *      "/projets-type/{id}/since-{sinceYear}",
     *      methods={"GET"},
     *      requirements={"sinceYear"="\d{4}"},
     *      name="api_multisociete_dashboard_general_projets_type"
     * )
     */
    public function getProjetsTypesSinceYear(
        SocieteUser $societeUser,
        int $sinceYear,
        ProjetRepository $projetRepository
    ) {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $projets = $societeUser->isAdminFo()
            ? $projetRepository->findAllProjectsPerSociete($societeUser->getSociete(), $sinceYear)
            : $projetRepository->findAllForUserSinceYear($societeUser, RoleProjet::OBSERVATEUR, $sinceYear)
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
