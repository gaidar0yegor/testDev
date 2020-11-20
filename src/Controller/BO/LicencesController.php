<?php

namespace App\Controller\BO;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_BO_ADMIN")
 */
class LicencesController extends AbstractController
{
    /**
     * @Route("/licences", name="licences_")
     */
    public function licencesDistribuees()
    {
        $licences_distribuees = [];
        return $this->render('licences/licences_distribuees.html.twig', [
            'licences_distribuees' => $licences_distribuees,
        ]);
    }
}
