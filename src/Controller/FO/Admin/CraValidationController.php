<?php

namespace App\Controller\FO\Admin;

use App\Repository\UserRepository;
use DateTime;
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
     *      name="app_fo_admin_cra_validation",
     *      requirements={"year"="\d{4}"}
     * )
     */
    public function index(string $year = null, UserRepository $userRepository)
    {
        if (null === $year) {
            $year = intval(date('Y'));
        }

        $users = $userRepository->findWithCra(
            $this->getUser()->getSociete(),
            $year
        );

        return $this->render('cra_validation/index.html.twig', [
            'users' => $users,
            'year' => $year,
        ]);
    }
}
