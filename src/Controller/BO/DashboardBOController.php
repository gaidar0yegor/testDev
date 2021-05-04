<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class DashboardBOController extends AbstractController
{
    /**
     * @Route("/dashboard", name="app_bo_dashboard")
     */
    public function dashboard(UserRepository $userRepository)
    {
     
        return $this->render('bo/dashboard/dashboard.html.twig',[
            'user' => $userRepository->findAll(),
        ]);
    }
}