<?php

namespace App\Controller\API;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\Repository\CraRepository;
use App\Repository\TempsPasseRepository;
use App\Security\Voter\SameSocieteVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Returns statistics about users or projets
 * to generate charts dedicated to the admin.
 *
 * @IsGranted("SOCIETE_ADMIN")
 * @Route("/api/stats/admin")
 */
class AdminStatsController extends AbstractController
{
    /**
     * Retourne les temps passés par un user sur ses projets sur une année.
     * Exemple :
     * {
     *      "months": [
     *          {
     *              "RDI-M": 35,
     *              "Group": 5
     *          },
     *          {
     *              "RDI-M": 40,
     *              "Group": 2
     *          },
     *          ...
     *      ]
     * }
     *
     * @Route(
     *      "/temps-par-projet/{id}/{year}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}"},
     *      name="api_stats_admin_temps_user_projets"
     * )
     */
    public function getTempsUserParProjet(
        string $year,
        SocieteUser $societeUser,
        CraRepository $craRepository
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        $cras = $craRepository->findCrasByUserAndYear($societeUser, $year);
        $data = [];

        for ($i = 0; $i < 12; ++$i) {
            $data[$i] = [];
        }

        foreach ($cras as $cra) {
            $tempsPasses = [];

            foreach ($cra->getTempsPasses() as $tempsPasse) {
                $tempsPasses[$tempsPasse->getProjet()->getAcronyme()] = $tempsPasse->getPourcentage();
            }

            $data[intval($cra->getMois()->format('m')) - 1] = $tempsPasses;
        }

        return new JsonResponse([
            'months' => $data,
        ]);
    }

    /**
     * Retourne les temps passés sur un projet par les contributeurs sur une année.
     * Exemple :
     * {
     *      "months": [
     *          {
     *              "User A": 35,
     *              "User B": 5
     *          },
     *          {
     *              "User A": 40,
     *              "User C": 2
     *          },
     *          ...
     *      ]
     * }
     *
     * @Route(
     *      "/temps-par-user/{id}/{year}",
     *      methods={"GET"},
     *      requirements={"year"="\d{4}"},
     *      name="api_stats_admin_temps_projet_users"
     * )
     */
    public function getTempsProjetParUsers(
        string $year,
        Projet $projet,
        TempsPasseRepository $tempsPasseRepository
    ) {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $projet);

        $tempsPasses = $tempsPasseRepository->findAllByProjetAndYear($projet, $year);
        $months = [];

        for ($i = 0; $i < 12; ++$i) {
            $months[$i] = [];
        }

        foreach ($tempsPasses as $tempsPasse) {
            $month = intval($tempsPasse->getCra()->getMois()->format('m')) - 1;
            $user = $tempsPasse->getCra()->getSocieteUser()->getUser()->getShortname();
            $percentage = $tempsPasse->getPourcentage();

            $months[$month][$user] = $percentage;
        }

        return new JsonResponse([
            'months' => $months,
        ]);
    }
}
