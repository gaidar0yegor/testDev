<?php

namespace App\Controller\CorpApp\FO;

use App\DTO\UpdatePassword;
use App\Entity\Fichier;
use App\Form\AvatarType;
use App\Form\MonCompteType;
use App\Form\SocieteUserSuperiorType;
use App\Form\UpdatePasswordType;
use App\Form\UserNotificationType;
use App\Notification\Event\SuperiorHierarchicalAddedEvent;
use App\Repository\SocieteUserActivityRepository;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/mon-compte")
 */
class MonCompteController extends AbstractController
{
    private UserContext $userContext;
    private EventDispatcherInterface $dispatcher;

    public function __construct(UserContext $userContext, EventDispatcherInterface $dispatcher)
    {
        $this->userContext = $userContext;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("", name="corp_app_fo_mon_compte")
     */
    public function monCompte(Request $request, EntityManagerInterface $em)
    {
        $notificationForm = $this->createForm(UserNotificationType::class, $this->userContext->getUser())
            ->handleRequest($request);

        if ($notificationForm->isSubmitted() && $notificationForm->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Vos préférences de notifications ont été mises à jour.');

            return $this->redirectToRoute('corp_app_fo_mon_compte');
        }

        if ($this->userContext->hasSocieteUser()){
            $mySuperiorForm = $this->createForm(SocieteUserSuperiorType::class, $this->userContext->getSocieteUser())
                ->handleRequest($request);

            if ($mySuperiorForm->isSubmitted() && $mySuperiorForm->isValid()) {
                $this->dispatcher->dispatch(new SuperiorHierarchicalAddedEvent($this->userContext->getSocieteUser()));

                $em->flush();

                $this->addFlash('success', 'Votre supérieur hiérarchique (N+1) a été mis à jour.');

                return $this->redirectToRoute('corp_app_fo_mon_compte');
            }
        }

        return $this->render('corp_app/mon_compte/mon_compte.html.twig', [
            'notificationForm' => $notificationForm->createView(),
            'mySuperiorForm' => isset($mySuperiorForm) ? $mySuperiorForm->createView() : null,
        ]);
    }

    /**
     * @Route("/modifier", name="corp_app_fo_mon_compte_modifier")
     */
    public function monCompteModifier(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(MonCompteType::class, $this->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');

            return $this->redirectToRoute('corp_app_fo_mon_compte');
        }

        return $this->render('corp_app/mon_compte/mon_compte_modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modifier/avatar", name="corp_app_fo_mon_compte_modifier_avatar")
     */
    public function monCompteModifierAvatar(Request $request, EntityManagerInterface $em, UserContext $userContext)
    {
        $fichier = new Fichier();
        $form = $this->createForm(AvatarType::class, $fichier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userContext->getUser()->setAvatar($fichier);

            $em->flush();

            $this->addFlash('success', 'Votre avatar a été mis à jour.');

            return $this->redirectToRoute('corp_app_fo_mon_compte');
        }

        return $this->render('corp_app/mon_compte/mon_compte_modifier_avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/changer-mot-de-passe", name="corp_app_fo_mon_compte_update_password")
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

                return $this->redirectToRoute('corp_app_fo_mon_compte');
            }

            $this->addFlash('error', 'Votre ancien mot de passe saisi n\'est pas le bon.');
        }

        return $this->render('corp_app/mon_compte/update_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activite", name="corp_app_fo_mon_compte_activite")
     */
    public function activite(SocieteUserActivityRepository $societeUserActivityRepository, UserContext $userContext)
    {
        return $this->render('corp_app/mon_compte/activity.html.twig', [
            'activities' => $societeUserActivityRepository->findBySocieteUser($userContext->getSocieteUser()),
        ]);
    }
}
