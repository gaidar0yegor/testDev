<?php

namespace App\Controller\CorpApp\FO\Equipe;

use App\Repository\SocieteUserRepository;
use App\MultiSociete\UserContext;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Voter\HasProductPrivilegeVoter;
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
     *      name="corp_app_fo_mon_equipe_cra_validation",
     *      requirements={"year"="\d{4}"}
     * )
     */
    public function index(string $year = null, SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::SOCIETE_HIERARCHICAL_SUPERIOR);

        if (null === $year) {
            $year = intval(date('Y'));
        }

        $societeUsers = $societeUserRepository->findWithCraOfMonEquipe(
            $userContext->getSocieteUser(),
            $year
        );

        return $this->render('corp_app/cra_validation/index.html.twig', [
            'societeUsers' => $societeUsers,
            'year' => $year,
        ]);
    }
}
