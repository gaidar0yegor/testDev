<?php

namespace App\Controller\FO;

use App\Entity\SocieteUser;
use App\Security\Voter\SameSocieteVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SocieteUserController extends AbstractController
{
    /**
     * @Route("/utilisateur/{id}", name="app_fo_societe_user")
     */
    public function compteUtilisateur(SocieteUser $societeUser)
    {
        $this->denyAccessUnlessGranted(SameSocieteVoter::NAME, $societeUser);

        return $this->render('utilisateurs_fo/view_user.html.twig', [
            'societeUser' => $societeUser,
        ]);
    }
}
