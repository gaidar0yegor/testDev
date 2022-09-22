<?php

namespace App\Controller\BO;

use App\Entity\Patchnote;
use App\Form\PatchnoteType;
use App\Repository\PatchnoteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Shivas\VersioningBundle\Service\VersionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatchnoteController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route(
     *     "/patchnote/liste",
     *     name="corp_app_bo_patchnote_list"
     * )
     */
    public function list(
        Request $request,
        PatchnoteRepository $patchnoteRepository
    )
    {
        $previewPatchnote = $request->query->has('preview')
            ? $patchnoteRepository->find($request->query->get('preview'))
            : null;
        $patchnotes = $patchnoteRepository->findAll();

        return $this->render('bo/patchnote/list.html.twig', [
            'patchnotes' => $patchnotes,
            'previewPatchnote' => $previewPatchnote,
        ]);
    }

    /**
     * @Route(
     *     "/patchnote/ajouter/{rdi_app}",
     *     name="corp_app_bo_patchnote_new",
     *     requirements={"rdi_app": "^corp_app|lab_app$"}
     * )
     */
    public function new(
        Request $request,
        string $rdi_app,
        VersionManager $versionManager,
        UserRepository $userRepository
    )
    {
        $existedPatchnote = $this->em->getRepository(Patchnote::class)->findOneBy(['rdiApp' => $rdi_app, 'version' => $versionManager->getVersion()]);

        if (null !== $existedPatchnote){
            $this->addFlash('warning','Patch note est déjà créée pour cette version !! Vous avez la possibilité de la modifier.');

            return $this->redirectToRoute('corp_app_bo_patchnote_update',[
                'id' => $existedPatchnote->getId()
            ]);
        }

        $patchnote = new Patchnote();
        try{
            $patchnote->setVersion($versionManager->getVersion());
            $patchnote->setRdiApp($rdi_app);
        } catch (InvalidArgumentException $e) {
            dd($e);
        }

        $form = $this->createForm(PatchnoteType::class, $patchnote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($patchnote);
            $userRepository->updatePatchnoteReaded();

            $this->em->flush();

            $this->addFlash('success','Patch note est ajouté avec succès');

            return $this->redirectToRoute('corp_app_bo_patchnote_list');
        }

        return $this->render('bo/patchnote/post_edit.html.twig', [
            'rdi_app' => $rdi_app,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/patchnote/modifier/{id}",
     *     name="corp_app_bo_patchnote_update"
     * )
     */
    public function update(
        Patchnote $patchnote,
        Request $request,
        UserRepository $userRepository
    )
    {
        $form = $this->createForm(PatchnoteType::class, $patchnote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($patchnote);
            $userRepository->updatePatchnoteReaded();

            $this->em->flush();

            $this->addFlash('success','Patch note est modifié avec succès');

            return $this->redirectToRoute('corp_app_bo_patchnote_list');
        }

        return $this->render('bo/patchnote/post_edit.html.twig', [
            'rdi_app' => $patchnote->getRdiApp(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/patchnote/supprimer/{id}", name="corp_app_bo_patchnote_delete")
     */
    public function delete(
        Patchnote $patchnote,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): Response {

        $msg = $patchnote->getVersion() . ' - ' . $translator->trans($patchnote->getRdiApp().'_name');

        $em->remove($patchnote);
        $em->flush();

        $this->addFlash('success','Patch note '. $msg .' est supprimé avec succès');

        return $this->redirectToRoute('corp_app_bo_patchnote_list');
    }
}
