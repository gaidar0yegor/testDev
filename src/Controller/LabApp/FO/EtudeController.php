<?php

namespace App\Controller\LabApp\FO;

use App\Entity\Fichier;
use App\Entity\LabApp\Etude;
use App\Entity\LabApp\FichierEtude;
use App\EtudeResourceInterface;
use App\Form\AvatarType;
use App\Form\LabApp\EtudeBannerType;
use App\Form\LabApp\EtudeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\MultiSociete\UserContext;

/**
 * @Route("/etudes")
 */
class EtudeController extends AbstractController
{
    /**
     * @Route("", name="lab_app_fo_etudes")
     */
    public function list(UserContext $userContext)
    {
        $etudes = $userContext->getUserBook()->getEtudes();

        return $this->render('lab_app/etude/list.html.twig', [
            'etudes'=> $etudes,
        ]);
    }

    /**
     * @Route("/creation", name="lab_app_fo_etude_creation")
     *
     *
     */
    public function creation(Request $request, UserContext $userContext) : Response
    {
        $etude = new Etude();

        $etude
            ->setUserBook($userContext->getUserBook())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->modify('+2 years'))
        ;

        $form = $this->createForm(EtudeType::class, $etude);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($etude);
            $em->flush();

            $this->addFlash('success', sprintf('L\'étude "%s" a été créée.', $etude->getTitle()));

            return $this->redirectToRoute('lab_app_fo_etude', [
                'id' => $etude->getId(),
            ]);
        }

        return $this->render('lab_app/etude/post_edit.html.twig', [
            'etude' => $etude,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="lab_app_fo_etude_modifier")
     *
     *
     */
    public function edition(Request $request, Etude $etude): Response
    {
        $this->denyAccessUnlessGranted('edit', $etude);

        $form = $this->createForm(EtudeType::class, $etude);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($etude);
            $em->flush();

            $this->addFlash('success', sprintf('L\'étude "%s" a été modifié.', $etude->getTitle()));

            return $this->redirectToRoute('lab_app_fo_etude', [
                'id' => $etude->getId(),
            ]);
        }

        return $this->render('lab_app/etude/post_edit.html.twig', [
            'etude' => $etude,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/banniere", name="lab_app_fo_etude_banner_modifier")
     */
    public function bannerEdit(Request $request, Etude $etude)
    {
        $this->denyAccessUnlessGranted('edit', $etude);

        $fichier = new Fichier();
        $form = $this->createForm(EtudeBannerType::class, $fichier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($fichier);
            $em->flush();

            $etude->setBanner($fichier);
            $em->persist($etude);
            $em->flush();

            $this->addFlash('success', 'La bannière de l\'étude a été mise à jour.');

            return $this->redirectToRoute('lab_app_fo_etude',[
                'id' => $etude->getId()
            ]);
        }

        return $this->render('lab_app/etude/edit_banner.html.twig', [
            'etude' => $etude,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="lab_app_fo_etude", requirements={"id"="\d+"})
     */
    public function ficheEtude(
        UserContext $userContext,
        Etude $etude
    ) {
        $this->denyAccessUnlessGranted('view', $etude);

        return $this->render('lab_app/etude/fiche_etude.html.twig', [
            'etude' => $etude,
            'userCanEditEtude' => $this->isGranted('edit', $etude),
            'userCanAddNote' => $this->isGranted(EtudeResourceInterface::CREATE, $etude)
        ]);
    }
}
