<?php

namespace App\Controller\FO;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Form\FaitMarquantType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class FaitMarquantController extends AbstractController
{
    /**
     * @Route("/fiche/projet/{projetId}/fait-marquants/ajouter", name="fait_marquant_ajouter", methods={"GET","POST"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function new(Projet $projet, Request $request): Response
    {
        $this->denyAccessUnlessGranted('create_fait_marquant', $projet);

        $faitMarquant = new FaitMarquant();
        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $faitMarquant
                ->setProjet($projet)
                ->setCreatedBy($this->getUser())
                ->setDate(new \DateTime())
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($faitMarquant);
            $em->flush();

            return $this->redirectToRoute('fiche_projet_', [
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
     * @Route("/fait-marquants/{id}/modifier", name="fait_marquant_modifier", methods={"GET","POST"})
     */
    public function edit(Request $request, FaitMarquant $faitMarquant): Response
    {
        $this->denyAccessUnlessGranted('edit', $faitMarquant);

        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('fiche_projet_', [
                'id' => $faitMarquant->getProjet()->getId(),
            ]);
        }

        return $this->render('fait_marquant/edit.html.twig', [
            'faitMarquant' => $faitMarquant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fait-marquants/{id}", name="fait_marquant_delete", methods={"DELETE"})
     */
    public function delete(Request $request, FaitMarquant $faitMarquant): Response
    {
        $this->denyAccessUnlessGranted('delete', $faitMarquant);

        if ($this->isCsrfTokenValid('delete'.$faitMarquant->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($faitMarquant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('fiche_projet_', [
            'id' => $faitMarquant->getProjet()->getId(),
        ]);
    }
}
