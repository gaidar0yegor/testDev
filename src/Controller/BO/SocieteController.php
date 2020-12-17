<?php

namespace App\Controller\BO;

use App\DTO\InitSociete;
use App\Entity\Societe;
use App\Entity\User;
use App\Form\InitSocieteType;
use App\Repository\SocieteRepository;
use App\Service\Invitator;
use App\Service\SocieteInitializer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SocieteController extends AbstractController
{
    /**
     * @Route("/societes", name="app_bo_societes")
     */
    public function societes(SocieteRepository $societeRepository)
    {
        return $this->render('bo/societes/societes.html.twig', [
            'societes' => $societeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/societes/{id}", name="app_bo_societe", requirements={"id"="\d+"})
     */
    public function societe(Societe $societe)
    {
        $adminReceivedInvitation = false;

        foreach ($societe->getAdmins() as $admin) {
            if (null !== $admin->getInvitationSentAt()) {
                $adminReceivedInvitation = true;
                break;
            }
        }

        if (!$adminReceivedInvitation) {
            $this->addFlash('warning', "
                Aucun administrateur de cette société n'a encore pas reçu de notifications.
                Envoyez un email d'invitation depuis cette page
                afin qu'il puisse finaliser son inscription !
            ");
        }

        return $this->render('bo/societes/societe.html.twig', [
            'societe' => $societe,
        ]);
    }

    /**
     * @Route(
     *      "/societes/{societeId}/envoi-invitation/{userId}",
     *      name="app_bo_societe_invite",
     *      methods={"POST"},
     *      requirements={"id"="\d+"}
     * )
     *
     * @ParamConverter("societe", options={"id" = "societeId"})
     * @ParamConverter("user", options={"id" = "userId"})
     */
    public function societeSendInvitation(Request $request, Societe $societe, User $user, Invitator $invitator, EntityManagerInterface $em)
    {
        if (!$this->isCsrfTokenValid('send-invitation-admin', $request->get('token'))) {
            throw new BadRequestHttpException('Csrf token invalid');
        }

        $invitator->sendInvitation($user, $this->getUser());
        $em->flush();

        $this->addFlash('success', sprintf(
            'Un email avec un lien d\'invitation a été envoyé à l\'administrateur "%s".',
            $user->getEmail()
        ));

        return $this->redirectToRoute('app_bo_societe', [
            'id' => $societe->getId(),
        ]);
    }

    /**
     * @Route("/societes/creer", name="app_bo_societes_creer")
     */
    public function create(
        Request $request,
        Invitator $invitator,
        SocieteInitializer $societeInitializer,
        EntityManagerInterface $em
    ) {
        $initSociete = new InitSociete();
        $form = $this->createForm(InitSocieteType::class, $initSociete);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societe = $invitator->initSociete($initSociete);
            $invitator->check($societe, $form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($societe);
            $em->flush();

            $societeInitializer->initializeCronJobs($societe);
            $em->flush();

            $this->addFlash('success', sprintf(
                    'La société "%s" a bien été créée.',
                    $societe->getRaisonSociale()
            ));

            return $this->redirectToRoute('app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        return $this->render('bo/societes/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
