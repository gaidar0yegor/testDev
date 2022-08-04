<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("", name="corp_app_bo_home")
     */
    public function home()
    {
        return $this->redirectToRoute('corp_app_bo_dashboard');
    }
}
