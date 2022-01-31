<?php

namespace App\Controller\FO\Equipe;

use App\Repository\SocieteUserRepository;
use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/validations")
 */
class CraValidationController extends AbstractController
{
    /**
     * @Route(
     *      "/validations/{year}",
     *      name="app_fo_mon_equipe_cra_validation",
     *      requirements={"year"="\d{4}"}
     * )
     */
    public function index(string $year = null, SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        if (null === $year) {
            $year = intval(date('Y'));
        }

        $societeUsers = $societeUserRepository->findWithCraOfMonEquipe(
            $userContext->getSocieteUser(),
            $year
        );

        return $this->render('cra_validation/index.html.twig', [
            'societeUsers' => $societeUsers,
            'year' => $year,
        ]);
    }
}
