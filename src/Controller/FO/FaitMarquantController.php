<?php

namespace App\Controller\FO;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Form\FaitMarquantType;
use App\ProjetResourceInterface;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaitMarquantController extends AbstractController
{
    /**
     * @Route("/projets/{projetId}/fait-marquants/ajouter", name="app_fo_fait_marquant_ajouter", methods={"GET","POST"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function new(
        Projet $projet,
        Request $request,
        EntityManagerInterface $em,
        UserContext $userContext
    ): Response {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::CREATE, $projet);

        $faitMarquant = new FaitMarquant();
        $faitMarquant
            ->setProjet($projet)
            ->setCreatedBy($userContext->getSocieteUser())
            ->setDate(new \DateTime())
        ;

        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($faitMarquant);
            $em->flush();

            $this->addFlash('success', sprintf(
                'Le fait marquant "%s" a été ajouté au projet.',
                $faitMarquant->getTitre()
            ));

            return $this->redirectToRoute('app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('fait_marquant/new.html.twig', [
            'fait_marquant' => $faitMarquant,
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fait-marquants/{id}/modifier", name="app_fo_fait_marquant_modifier", methods={"GET","POST"})
     */
    public function edit(Request $request, FaitMarquant $faitMarquant, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::EDIT, $faitMarquant);

        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', sprintf(
                'Le fait marquant "%s" a été modifié.',
                $faitMarquant->getTitre()
            ));

            return $this->redirectToRoute('app_fo_projet', [
                'id' => $faitMarquant->getProjet()->getId(),
                '_fragment' => 'fait-marquant-'.$faitMarquant->getId(),
            ]);
        }

        return $this->render('fait_marquant/edit.html.twig', [
            'faitMarquant' => $faitMarquant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fait-marquants/{id}", name="app_fo_fait_marquant_delete", methods={"DELETE"})
     */
    public function delete(Request $request, FaitMarquant $faitMarquant, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(ProjetResourceInterface::DELETE, $faitMarquant);

        if ($this->isCsrfTokenValid('delete'.$faitMarquant->getId(), $request->request->get('_token'))) {
            $em->remove($faitMarquant);
            $em->flush();

            $this->addFlash('warning', sprintf(
                'Le fait marquant "%s" a été supprimé.',
                $faitMarquant->getTitre()
            ));
        }

        return $this->redirectToRoute('app_fo_projet', [
            'id' => $faitMarquant->getProjet()->getId(),
        ]);
    }
}
