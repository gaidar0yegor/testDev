<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SyntheseFoController extends AbstractController
{
    /**
     * @Route("/synthese/fo", name="synthese_fo_")
     */
    public function AfficherIndicateurs()
    {
        return $this->render('synthese_fo/synthese_fo.html.twig', [
            'controller_name' => 'SyntheseFoController',
        ]);
    }
}
