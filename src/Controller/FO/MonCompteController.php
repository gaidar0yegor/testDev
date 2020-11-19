<?php

namespace App\Controller\FO;

use App\DTO\UpdatePassword;
use App\Form\Custom\RepeatedPasswordType;
use App\Form\UpdatePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
