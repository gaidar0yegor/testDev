<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\ProjetRepository;
use App\Repository\SocieteRepository;

class DashboardBOController extends AbstractController
{

    /**
    * @var UserRepository
    */

    private $userRepository;

    public function __construct(ProjetRepository $projetRepository,UserRepository $userRepository, SocieteRepository $societeRepository)
    {
        $this->userRepository = $userRepository;
        $this->projetRepository = $projetRepository;
        $this->societeRepository = $societeRepository;
    }

    /**
     * @Route("/dashboard", name="app_bo_dashboard")
     */
    public function dashboardUser(ProjetRepository $projetRepository, UserRepository $userRepository, SocieteRepository $societeRepository)
    {
        $index = 0;

        $userCreatedAt = $this->userRepository->findCreatedAt((new \DateTime())->format('Y'));

        $userData = [];  

        for ($index = 1; $index < 13; $index++) {
            $userData[$index] = 0;
        }

        foreach ($userCreatedAt as $user) {
            $userData[$user['mois']] = $user['total'];
        }

        $projetCreatedAt = $this->projetRepository->findCreatedAt((new \DateTime())->format('Y'));

        $projetData = [];

        for ($index = 1; $index < 13; $index++) {
            $projetData[$index] = 0;
        }

        foreach ($projetCreatedAt as $projet) {
            $projetData[$projet['mois']] = $projet['total'];
        }

        $societeCreatedAt = $this->societeRepository->findCreatedAt((new \DateTime())->format('Y'));

        $societeData = [];

        for ($index = 1; $index < 13; $index++) {
            $societeData[$index] = 0;
        }

        foreach ($societeCreatedAt as $societe) {
            $societeData[$societe['mois']] = $societe['total'];
        }
        
        return $this->render('bo/dashboard/dashboard.html.twig',[
            'user' => $userRepository->findAll(),
            'projet' => $projetRepository->findAll(),
            'societe' => $societeRepository->findAll(),
            'userCreatedAt' => $userData,
            'projetCreatedAt' => $projetData,
            'societeCreatedAt' => $societeData,
        ]);
    }

}