<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;
use App\Form\FaitMarquantType;
use App\Notification\Event\FaitMarquantRemovedEvent;
use App\Notification\Event\FaitMarquantRestoredEvent;
use App\ProjetResourceInterface;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FaitMarquantController extends AbstractController
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("/projets/{projetId}/fait-marquants/ajouter", name="corp_app_fo_fait_marquant_ajouter", methods={"GET","POST"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function new(
        Projet $projet,
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UserContext $userContext
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::CREATE, $projet);

        $faitMarquant = new FaitMarquant();
        $faitMarquant
            ->setProjet($projet)
            ->setCreatedBy($userContext->getSocieteUser())
            ->setDate(new \DateTime())
        ;

        if ($request->query->has('link_task')){
            $faitMarquant->setProjetPlanningTask($em->getRepository(ProjetPlanningTask::class)->findOneBy([
                'id' => $request->query->get('link_task'),
                'projetPlanning' => $projet->getProjetPlanning(),
            ]));
        }

        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($faitMarquant);
            $em->flush();

            $this->addFlash('success', $translator->trans(
                'Le fait marquant "{titre_fait_marquant}" a été ajouté au projet.',
                [
                    'titre_fait_marquant' => $faitMarquant->getTitre(),
                ]
            ));

            return $this->redirectToRoute('corp_app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/fait_marquant/new.html.twig', [
            'fait_marquant' => $faitMarquant,
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fait-marquants/{id}/modifier", name="corp_app_fo_fait_marquant_modifier", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        FaitMarquant $faitMarquant,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::EDIT, $faitMarquant);

        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', $translator->trans(
                'Le fait marquant "{titre_fait_marquant}" a été modifié.',
                [
                    'titre_fait_marquant' => $faitMarquant->getTitre(),
                ]
            ));

            return $this->redirectToRoute('corp_app_fo_projet', [
                'id' => $faitMarquant->getProjet()->getId(),
                '_fragment' => 'fait-marquant-'.$faitMarquant->getId(),
            ]);
        }

        return $this->render('corp_app/fait_marquant/edit.html.twig', [
            'faitMarquant' => $faitMarquant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fait-marquants/{id}", name="corp_app_fo_fait_marquant_delete", methods={"DELETE"})
     */
    public function delete(
        Request $request,
        FaitMarquant $faitMarquant,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        UserContext $userContext
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::DELETE, $faitMarquant);

        if ($this->isCsrfTokenValid('delete'.$faitMarquant->getId(), $request->request->get('_token'))) {
            $faitMarquant->setTrashedAt(new \DateTime());
            $faitMarquant->setTrashedBy($userContext->getSocieteUser());
            $this->dispatcher->dispatch(new FaitMarquantRemovedEvent($faitMarquant));

            $em->flush();

            $this->addFlash('success', $translator->trans(
                'Le fait marquant "{titre_fait_marquant}" a été supprimé.',
                [
                    'titre_fait_marquant' => $faitMarquant->getTitre(),
                ]
            ));
        }

        return $this->redirectToRoute('corp_app_fo_projet', [
            'id' => $faitMarquant->getProjet()->getId(),
        ]);
    }

    /**
     * @Route("/projets/{id}/fait-marquants/corbeille", name="corp_app_fo_fait_marquant_trash", methods={"GET"})
     */
    public function trash(
        Request $request,
        Projet $projet,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('edit', $projet);

        $faitMarquants = $em->getRepository(FaitMarquant::class)->findTrashItems($projet);

        return $this->render('corp_app/fait_marquant/trash.html.twig', [
            'projet' => $projet,
            'faitMarquants' => $faitMarquants,
        ]);
    }

    /**
     * @Route("/fait-marquants/restaurer/{id}", name="corp_app_fo_fait_marquant_restore", methods={"GET"})
     */
    public function restore(
        Request $request,
        FaitMarquant $faitMarquant,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::DELETE, $faitMarquant);

        $faitMarquant->setTrashedAt(null);
        $faitMarquant->setTrashedBy(null);
        $this->dispatcher->dispatch(new FaitMarquantRestoredEvent($em->getRepository(FaitMarquant::class)->find($faitMarquant->getId())));

        $em->flush();

        $this->addFlash('success', $translator->trans(
            'Le fait marquant "{titre_fait_marquant}" a été restauré',
            [
                'titre_fait_marquant' => $faitMarquant->getTitre(),
            ]
        ));

        return $this->redirectToRoute('corp_app_fo_projet', [
            'id' => $faitMarquant->getProjet()->getId(),
        ]);
    }
}
