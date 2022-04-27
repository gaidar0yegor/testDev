<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("", name="app_bo_home")
     */
    public function home()
    {
        return $this->redirectToRoute('app_bo_dashboard');
    }
}
