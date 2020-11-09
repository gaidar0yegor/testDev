<?php

namespace App\Controller\FO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MonCompteController extends AbstractController
{
    /**
     * @Route("/mon-compte", name="mon_compte")
     */
    public function monCompte()
    {
        return $this->render('mon_compte/mon_compte.html.twig');
    }
}
