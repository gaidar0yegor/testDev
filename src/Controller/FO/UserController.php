<?php

namespace App\Controller\FO;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/utilisateur/{id}", name="app_fo_user")
     */
    public function compteUtilisateur(User $user)
    {
        $this->denyAccessUnlessGranted('same_societe', $user);

        return $this->render('utilisateurs_fo/view_user.html.twig', [
            'user' => $user,
        ]);
    }
}
