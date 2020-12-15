<?php

namespace App\Controller\FO\Admin;

use App\Form\SocieteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/societe")
 */
class SocieteController extends AbstractController
{
    /**
     * @Route("", name="app_fo_admin_societe_show", methods={"GET"})
     */
    public function show(): Response
    {
        return $this->render('societe/show.html.twig', [
            'societe' => $this->getUser()->getSociete(),
        ]);
    }

    /**
     * @Route("/modifier", name="app_fo_admin_societe_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $societe = $this->getUser()->getSociete();
        $form = $this->createForm(SocieteType::class, $societe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Société modifiée avec succès.');

            return $this->redirectToRoute('app_fo_admin_societe_show');
        }

        return $this->render('societe/edit.html.twig', [
            'societe' => $societe,
            'form' => $form->createView(),
        ]);
    }
}
