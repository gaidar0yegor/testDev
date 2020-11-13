<?php

namespace App\Controller\FO;

use App\Entity\Societe;
use App\Form\SocieteType;
use App\Repository\SocieteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/societe")
 * @IsGranted("ROLE_FO_ADMIN")
 */
class SocieteController extends AbstractController
{
    /**
     * @Route("/", name="fo_societe_show", methods={"GET"})
     */
    public function show(): Response
    {
        return $this->render('societe/show.html.twig', [
            'societe' => $this->getUser()->getSociete(),
        ]);
    }

    /**
     * @Route("/modifier", name="fo_societe_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $societe = $this->getUser()->getSociete();
        $form = $this->createForm(SocieteType::class, $societe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Société modifiée avec succès.');

            return $this->redirectToRoute('fo_societe_show');
        }

        return $this->render('societe/edit.html.twig', [
            'societe' => $societe,
            'form' => $form->createView(),
        ]);
    }
}
