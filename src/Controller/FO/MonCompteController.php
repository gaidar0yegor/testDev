<?php

namespace App\Controller\FO;

use App\DTO\UpdatePassword;
use App\Form\MonCompteType;
use App\Form\UpdatePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MonCompteController extends AbstractController
{
    /**
     * @Route("/mon-compte", name="mon_compte")
     */
    public function monCompte()
    {
        return $this->render('mon_compte/mon_compte.html.twig');
    }

    /**
     * @Route("/mon-compte/modifier", name="app_fo_mon_compte_modifier")
     */
    public function monCompteModifier(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(MonCompteType::class, $this->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');

            return $this->redirectToRoute('mon_compte');
        }

        return $this->render('mon_compte/mon_compte_modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mon-compte/changer-mot-de-passe", name="app_fo_mon_compte_update_password")
     */
    public function updatePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $updatePassword = new UpdatePassword();
        $form = $this->createForm(UpdatePasswordType::class, $updatePassword);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $validOldPassword = $passwordEncoder->isPasswordValid($user, $updatePassword->getOldPassword());

            if ($validOldPassword) {
                $encodedPassword = $passwordEncoder->encodePassword($user, $updatePassword->getNewPassword());

                $user->setPassword($encodedPassword);

                $em->flush();

                $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

                return $this->redirectToRoute('mon_compte');
            }

            $this->addFlash('danger', 'Votre ancien mot de passe saisis n\'est pas le bon.');
        }

        return $this->render('mon_compte/update_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
