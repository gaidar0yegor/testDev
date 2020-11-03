<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\UploadType;
use App\Entity\FichierProjet;
use App\Repository\FichiersProjetRepository;

class FichierController extends AbstractController
{
    /**
     * @Route("/liste/fichiers", name="liste_fichiers_")
     */
    public function listeFichiers(FichiersProjetRepository $fr)
    {
        $liste_fichiers = $fr->findAll();
        return $this->render('fichier/liste_fichiers.html.twig', [
            'liste_fichiers' => $liste_fichiers,
            // 'controller_name' => 'FichierController',
        ]);
    }

    /**
     * @Route("/ajout/fichier", name="ajout_fichier_")
     * @param Request $rq
     * @return Response
     */
    public function uploadFichiers(Request $request): Response
    {
        $fichierProjet = new FichierProjet();
        $form = $this->createForm(UploadType::class, $fichierProjet);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $fichierProjet->getNomFichier(); // Récupérer le fichier dans le champ "nom_fichier" de l'entité "FichierProjet"
            $fileName = md5(uniqid()).'.'.$file->guessExtension(); // uniqid = faire un Id unique
            $file->move($this->getParameter('upload_directory'), $fileName);
            $fichierProjet->setNomFichier($fileName);

            return $this->redirectToRoute('liste_fichiers_');
        } 

        return $this->render('fichier/infos_fichier.html.twig', [
            'form' => $form->createView(),
            // 'controller_name' => 'FichierController',
        ]);
    }

}
