<?php

namespace App\Controller\LabApp\FO\Admin;

use App\Form\LabApp\LaboType;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/labo")
 */
class LaboController extends AbstractController
{
    /**
     * @Route("", name="lab_app_fo_admin_lab_show", methods={"GET"})
     */
    public function show(UserContext $userContext): Response
    {
        return $this->render('lab_app/labo/show.html.twig', [
            'labo' => $userContext->getUserBook()->getLabo(),
        ]);
    }

    /**
     * @Route("/modifier", name="lab_app_fo_admin_labo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EntityManagerInterface $em, UserContext $userContext): Response
    {
        $labo = $userContext->getUserBook()->getLabo();
        $form = $this->createForm(LaboType::class, $labo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Laboratoire modifié avec succès.');

            return $this->redirectToRoute('lab_app_fo_admin_lab_show');
        }

        return $this->render('lab_app/labo/edit.html.twig', [
            'labo' => $labo,
            'form' => $form->createView(),
        ]);
    }
}
