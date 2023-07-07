<?php

namespace App\Controller\CorpApp\FO\Admin;

use App\Entity\Activity;
use App\Entity\Fichier;
use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\File\FileHandler\ProjectFileHandler;
use App\Form\AvatarType;
use App\Form\SocieteType;
use App\MultiSociete\UserContext;
use App\ProjetResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/societe")
 */
class SocieteController extends AbstractController
{
    /**
     * @Route("", name="corp_app_fo_admin_societe_show", methods={"GET"})
     */
    public function show(UserContext $userContext): Response
    {
        return $this->render('corp_app/societe/show.html.twig', [
            'societe' => $userContext->getSocieteUser()->getSociete(),
        ]);
    }

    /**
     * @Route("/modifier", name="corp_app_fo_admin_societe_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EntityManagerInterface $em, UserContext $userContext): Response
    {
        $societe = $userContext->getSocieteUser()->getSociete();
        $form = $this->createForm(SocieteType::class, $societe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Société modifiée avec succès.');

            return $this->redirectToRoute('corp_app_fo_admin_societe_show');
        }

        return $this->render('corp_app/societe/edit.html.twig', [
            'societe' => $societe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modifier/logo", name="corp_app_fo_admin_societe_logo_edit")
     */
    public function logoEdit(Request $request, EntityManagerInterface $em, UserContext $userContext)
    {
        $fichier = new Fichier();
        $form = $this->createForm(AvatarType::class, $fichier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societe = $userContext->getSocieteUser()->getSociete();

            $em->persist($fichier);
            $em->flush();

            $societe->setLogo($fichier);
            $em->persist($societe);
            $em->flush();

            $this->addFlash('success', 'Votre logo a été mis à jour.');

            return $this->redirectToRoute('corp_app_fo_admin_societe_show');
        }

        return $this->render('corp_app/societe/edit_logo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *      "modifier/couleur",
     *      name="corp_app_fo_admin_societe_code_color_edit",
     *      methods={"POST"}
     * )
     */
    public function codeColorEdit(
        Request $request,
        EntityManagerInterface $em,
        UserContext $userContext
    ) {
        $societe = $userContext->getSocieteUser()->getSociete();

        if ($request->request->has('code_color')){
            $societe->setColorCode($request->request->get('code_color'));
            $em->persist($societe);
            $em->flush();
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        } else {
            return new JsonResponse(null, JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
