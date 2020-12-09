<?php

namespace App\Controller\FO;

use App\Role;
use App\Entity\Projet;
use App\Form\ProjetFormType;
use App\Entity\ProjetParticipant;
use App\ProjetResourceInterface;
use App\Repository\ProjetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ProjetParticipantRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;

class ProjetController extends AbstractController
{
    private Pdf $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * Affichage de tous les projets de la société
     * @Route("/tous-les-projets", name="app_fo_projet_admin_projets_")
     *
     * @IsGranted("ROLE_FO_ADMIN")
     */
    public function listerProjetAdmin(ProjetRepository $projetRepository)
    {
        $allProjectsOfSociete = $projetRepository->findAllProjectsPerSociete($this->getUser()->getSociete());
        return $this->render('projets/admin_liste_projets.html.twig', [
            'projets'=> $allProjectsOfSociete,
        ]);
    }

    /**
     * @Route("/projets", name="projets_")
     */
    public function listerProjet(ProjetParticipantRepository $projetParticipantRepository)
    {
        return $this->render('projets/liste_projets.html.twig', [
            'participes'=> $projetParticipantRepository->findAllForUser($this->getUser()),
        ]);

    }
 
    /**
     * @Route("/infos_projet", name="infos_projet_")
     *
     * @IsGranted("ROLE_FO_CDP")
     */
    public function creation(Request $rq) : Response
    {
        $projet = new Projet();
        $participant = new ProjetParticipant();
        $participant
            ->setUser($this->getUser())
            ->setRole(Role::CDP)
        ;

        $projet
            ->addProjetParticipant($participant)
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->modify('+2 years'))
        ;

        $form = $this->createForm(ProjetFormType::class, $projet);

        $form->handleRequest($rq);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', sprintf('Le projet "%s" a été créé.', $projet->getTitre()));
            return $this->redirectToRoute('fiche_projet_', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/saisie_infos_projet.html.twig', [
            'form' => $form->createView(),
            'bouton' => "Soumettre",
            'attr' => [
                'class' =>'btn btn-success',
            ]
        ]);
    }

    /**
     * @Route("/projets/{id}/edition", name="projet_edition")
     */
    public function edition(Request $request, Projet $projet): Response
    {
        $this->denyAccessUnlessGranted('edit', $projet);

        $form = $this->createForm(ProjetFormType::class, $projet);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', sprintf('Le projet "%s" a été modifié.', $projet->getTitre()));
            return $this->redirectToRoute('fiche_projet_', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projets/edition_projet.html.twig', [
            'projet' => $projet,
            'bouton' => "Modifier",
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fiche/projet/{id}", name="fiche_projet_")
     */
    public function ficheProjet(Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('projets/fiche_projet.html.twig', [
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet),
            'userCanAddFaitMarquant' => $this->isGranted(ProjetResourceInterface::CREATE, $projet),
        ]);
    }

    /**
     * @Route("/generate/projet/{id}", name="generate_fiche_projet_")
     */
    public function ficheProjetGeneratePdf(Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        $sheetHtml = $this->renderView('projets/pdf/pdf_fiche_projet.html.twig', [
            'projet' => $projet,
        ]);

        return $this->createPdfResponse($sheetHtml);
    }

    private function createPdfResponse(string $htmlContent, string $filename = 'projet.pdf'): PdfResponse
    {
        $options = [
            'margin-top'    => 15,
            'margin-right'  => 15,
            'margin-bottom' => 15,
            'margin-left'   => 15,
        ];
        $result = $this->pdf->getOutputFromHtml($htmlContent, $options);
        return new PdfResponse($result, $filename);
    }
}
