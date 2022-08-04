<?php

namespace App\Controller\CorpApp\FO\Admin;

use App\Repository\SocieteUserRepository;
use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("")
 */
class CraValidationController extends AbstractController
{
    /**
     * @Route(
     *      "/validations/{year}",
     *      name="corp_app_fo_admin_cra_validation",
     *      requirements={"year"="\d{4}"}
     * )
     */
    public function index(string $year = null, SocieteUserRepository $societeUserRepository, UserContext $userContext)
    {
        if (null === $year) {
            $year = intval(date('Y'));
        }

        $societeUsers = $societeUserRepository->findWithCra(
            $userContext->getSocieteUser()->getSociete(),
            $year
        );

        return $this->render('corp_app/cra_validation/index.html.twig', [
            'societeUsers' => $societeUsers,
            'year' => $year,
        ]);
    }
}
