<?php

namespace App\Controller\API;

use App\Repository\ProjetRepository;
use App\Role;
use App\Service\CraService;
use App\Service\DateMonthService;
use App\Service\ParticipantService;
use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * Retourne si l'utilisateur est à jour dans la saisie de ses temps.
     *
     * @Route(
     *      "/temps-du-mois",
     *      methods={"GET"},
     *      name="api_dashboard_temps_du_mois"
     * )
     */
    public function getTempsDuMois(CraService $craService, DateMonthService $dateMonthService)
    {
        $cra = $craService->loadCraForUser(
            $this->getUser(),
            $dateMonthService->getCurrentMonth()
        );

        return new JsonResponse([
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
        ]);
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
    public function getHeuresPasseesParProjet(int $year, StatisticsService $statisticsService)
    {
        $heuresParProjet = $statisticsService->calculateHeuresParProjetForUser(
            $this->getUser(),
            $year
        );

        return new JsonResponse($heuresParProjet);
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
        ParticipantService $participantService
    ) {
        $projets = $projetRepository->findAllForUserInYear($this->getUser(), Role::OBSERVATEUR, $year);
        $heuresParProjet = $statisticsService->calculateHeuresParProjet($this->getUser()->getSociete(), $year);

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
        ];

        foreach ($projets as $projet) {
            $userIsContributing = $participantService->hasRoleOnProjet(
                $this->getUser(),
                $projet,
                Role::CONTRIBUTEUR
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
            $this->getUser(),
            $year
        );

        return new JsonResponse($stats);
    }

    /**
     * Retourne les stats "Nb de projets en cours/terminés"
     * depuis une année N.
     *
     * @Route(
     *      "/projets-statuts/since-{year}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}"},
     *      name="api_dashboard_projets_statuts"
     * )
     */
    public function getProjetsStatuts(
        int $year,
        ProjetRepository $projetRepository,
        StatisticsService $statisticsService,
        ParticipantService $participantService
    ) {
        $now = new \DateTime();
        $projets = $this->getUser()->isAdminFo()
            ? $projetRepository->findAllProjectsPerSociete($this->getUser()->getSociete(), $year)
            : $projetRepository->findAllForUserSinceYear($this->getUser(), Role::OBSERVATEUR, $year)
        ;

        $stats = [
            'active' => 0,
            'finished' => 0,
        ];

        foreach ($projets as $projet) {
            if (null === $projet->getDateFin() || $projet->getDateFin() >= $now) {
                ++$stats['active'];
            } else {
                ++$stats['finished'];
            }
        }

        return new JsonResponse($stats);
    }
}
