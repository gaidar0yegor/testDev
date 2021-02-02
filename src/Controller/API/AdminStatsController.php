<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Repository\CraRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Returns statistics about users or projets
 * to generate charts dedicated to the admin.
 *
 * @IsGranted("ROLE_FO_ADMIN")
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
        User $user,
        CraRepository $craRepository
    ) {
        $this->denyAccessUnlessGranted('same_societe', $user);

        $cras = $craRepository->findCrasByUserAndYear($user, $year);
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
}
