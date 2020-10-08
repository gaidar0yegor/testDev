<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Repository\ProjetsRepository;


class ProjetsController extends AbstractController
{
    /**
     * @Route("/projets", name="projets")
     */
    public function index(ProjetsRepository $pr)
    {
        $liste_projets = $pr->findAll();
        return $this->render('projets/index.html.twig', [
            'liste_projets' => $liste_projets,
        ]);
    }
}
