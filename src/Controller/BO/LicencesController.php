<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LicencesRepository;


class LicencesController extends AbstractController
{
    /**
     * @Route("/licences", name="licences_")
     */
    public function index(LicencesRepository $lr)
    {
        $licences_distribuees = $lr ->findAll();
        return $this->render('licences/index.html.twig', [
            'licences_distribuees' => $licences_distribuees,
            // 'controller_name' => 'LicencesController',
        ]);
    }
}
