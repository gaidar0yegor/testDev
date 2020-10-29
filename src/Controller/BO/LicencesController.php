<?php

namespace App\Controller\BO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LicencesRepository;
use App\Entity\Societe;


class LicencesController extends AbstractController
{
    /**
     * @Route("/licences", name="licences_")
     */
    public function licencesDistribuees(LicencesRepository $lr)
    {
        $licences_distribuees = $lr ->findAll();
        return $this->render('licences/licences_distribuees.html.twig', [
            'licences_distribuees' => $licences_distribuees,
            // 'controller_name' => 'LicencesController',
        ]);
    }
}
