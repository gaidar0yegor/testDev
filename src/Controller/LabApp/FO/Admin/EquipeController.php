<?php

namespace App\Controller\LabApp\FO\Admin;

use App\MultiSociete\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/equipes")
 */
class EquipeController extends AbstractController
{
    /**
     * @Route("/liste", name="lab_app_fo_admin_equipes")
     *
     * @ParamConverter("labo", options={"id" = "laboId"})
     */
    public function list(UserContext $userContext)
    {
        $labo = $userContext->getUserBook()->getLabo();

        $equipes = $labo->getEquipes();

        return $this->render('lab_app/equipe/admin_list.html.twig', [
            'equipes'=> $equipes,
        ]);
    }
}
