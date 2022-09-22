<?php

namespace App\Controller;

use App\Repository\PatchnoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PatchnoteController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route(
     *     "/patchnote/historique/{rdi_app}",
     *     name="app_patchnote_historic",
     *     requirements={"rdi_app": "^corp_app|lab_app$"}
     * )
     */
    public function historic(
        string $rdi_app,
        PatchnoteRepository $patchnoteRepository
    )
    {
        $patchnotes = $patchnoteRepository->findBy(['rdiApp' => $rdi_app, 'isDraft' => false],['date' => 'DESC']);

        return $this->render('security/historic_patchnote.html.twig', [
            'rdi_app' => $rdi_app,
            'patchnotes' => $patchnotes,
        ]);
    }
}
