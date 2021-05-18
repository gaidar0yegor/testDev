<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\ProjetRepository;

class DashboardBOController extends AbstractController
{

    /**
    * @var UserRepository
    */

    private $userRepository;

    public function __construct(ProjetRepository $projetRepository,UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->projetRepository = $projetRepository;
    }

    /**
     * @Route("/dashboard", name="app_bo_dashboard")
     */
    public function dashboardUser(ProjetRepository $projetRepository, UserRepository $userRepository)
    {

        $userCreatedAt = $this->userRepository->findCreatedAt((new \DateTime())->format('Y'));

        $userData = [];

        for ($index = 1; $index < 13; $index++) {
            $userData[$index] = 0;
        }

        $index = 0;

        foreach ($userCreatedAt as $user) {
            $userData[$user['mois']] = $user['total'];
            $index++;
        }

        $projetCreatedAt = $this->projetRepository->findCreatedAt((new \DateTime())->format('Y'));

        $projetData = [];

        for ($index = 1; $index < 13; $index++) {
            $projetData[$index] = 0;
        }

        $index = 0;

        foreach ($projetCreatedAt as $projet) {
            $projetData[$projet['mois']] = $projet['total'];
            $index++;
        }
        
        return $this->render('bo/dashboard/dashboard.html.twig',[
            'user' => $userRepository->findAll(),
            'projet' => $projetRepository->findAll(),
            'userCreatedAt' => $userData,
            'projetCreatedAt' => $projetData,
        ]);
    }

}