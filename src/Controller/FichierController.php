<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\UploadType;
use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\Repository\FichiersProjetRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Nzo\FileDownloaderBundle\FileDownloader\FileDownloader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class FichierController extends AbstractController
{
    private $fileDownloader;

    public function __construct(FileDownloader $fileDownloader)
    {
        $this->fileDownloader = $fileDownloader;
    
        // without autowiring use: $this->get('nzo_file_downloader')
    }
   
    
    
    
    /**
     * @Route("/fiche/projet/{id}/liste/fichiers", name="liste_fichiers_")
     */
    public function listeFichiers(FichiersProjetRepository $fr, Projet $projet)
    {
        $liste_fichiers = $fr->findAll();
        return $this->render('fichier/liste_fichiers.html.twig', [
            'liste_fichiers' => $liste_fichiers,
            'projet' => $projet,
        ]);
    }

    /**
     * @Route("/fiche/projet/{id}/ajout/fichier", name="ajout_fichier_")
     * @param Request $rq
     * @return Response
     */
    public function uploadFichiers(Request $request, Projet $projet): Response
    {
        $fichierProjet = new FichierProjet();
        $form = $this->createForm(UploadType::class, $fichierProjet);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $fichierProjet->setNomFichier($fichierProjet->getFile()->getClientOriginalName());
            $fichierProjet->setUploadedBy($this->getUser());
            $fichierProjet->setProjet($projet);
            $fichierProjet->setDateUpload(new DateTime());
            
            //dd($fichierProjet);

            $fileName = md5(uniqid()).'.'.$fichierProjet->getFile()->guessExtension(); // uniqid = faire un Id unique
            $fichierProjet->getFile()->move($this->getParameter('upload_directory'), $fileName);
            
            $fichierProjet->setNomMd5($fileName);
            $em->persist($fichierProjet);
            $em->flush();   
            $this->addFlash('success', sprintf('Le fichier "%s" a été créé.', $fichierProjet->getNomFichier()));

            return $this->redirectToRoute('liste_fichiers_', [
                'id' => $projet->getid(),
            ]);
        } 

        return $this->render('fichier/infos_fichier.html.twig', [
            'form' => $form->createView(),
            // 'controller_name' => 'FichierController',
        ]);
    }

     /**
     * @Route("/fiche/projet/{projetId}/delete/fichier/{fichierProjetId}", name="efface_fichier_", methods={"DELETE"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     *
     * @IsGranted("ROLE_FO_CDP")
     */
    public function delete(Request $request, FichierProjet $fichierProjet, EntityManagerInterface $em, Projet $projet): Response
    {
        $this->denyAccessUnlessGranted('same_societe', $fichierProjet);
    
        //path
        $chemin = $this->getParameter('upload_directory').'/'.$fichierProjet->getNomMd5();
        unlink($chemin);

        $em->remove($fichierProjet);
        $em->flush();

        return $this->redirectToRoute('liste_fichiers_', [
            'id' => $projet->getid(),
        ]);
    }

    
     /**
     * @Route("/fiche/projet/{projetId}/dowload/fichier/{fichierProjetId}", name="telecharge_fichier_", methods={"DOWNLOAD"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("fichierProjet", options={"id" = "fichierProjetId"})
     *
     * @IsGranted("ROLE_FO_CDP")
     */
    public function download(Request $request, FichierProjet $fichierProjet, EntityManagerInterface $em, Projet $projet): Response
    {
        $this->denyAccessUnlessGranted('same_societe', $fichierProjet);
        //path
        $chemin = $this->getParameter('upload_directory').'/'.$fichierProjet->getNomMd5();
        return $this->fileDownloader->downloadFile($chemin);
        
        
       // return $fileDownloader1->downloadFileFromPublicFolder($chemin);
        //return new BinaryFileResponse($chemin);

    }

    public function downloadFileFromPublicFolder()
    {
         return $this->fileDownloader->downloadFile('myfolder/myfile.pdf');

       # change the name of the file when downloading:

         return $this->fileDownloader->downloadFile('myfolder/myfile.pdf', 'newName.pdf');
    }



}
