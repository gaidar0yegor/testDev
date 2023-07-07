<?php

namespace App\Controller\LabApp\FO;

use App\Entity\LabApp\Equipe;
use App\Form\LabApp\EquipeType;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/equipes")
 */
class EquipeController extends AbstractController
{
    /**
     * @Route("", name="lab_app_fo_equipes")
     */
    public function list(UserContext $userContext)
    {
        $equipes = $userContext->getUserBook()->getEquipes();

        return $this->render('lab_app/equipe/list.html.twig', [
            'equipes'=> $equipes,
        ]);
    }

    /**
     * @Route("/{id}", name="lab_app_fo_equipe", requirements={"id"="\d+"})
     */
    public function show(
        Equipe $equipe
    )
    {
        return $this->render('lab_app/equipe/show.html.twig', [
            'equipe'=> $equipe,
        ]);
    }

    /**
     * @Route("/gestion/{equipeId}", name="lab_app_fo_equipe_post_edit", defaults={"equipeId"=null})
     *
     * @ParamConverter("equipe", options={"id" = "equipeId"})
     *
     * @IsGranted("LABO_SENIOR")
     */
    public function index(
        Request $request,
        Equipe $equipe = null,
        UserContext $userContext,
        EntityManagerInterface $em
    ) {
        $labo = $userContext->getUserBook()->getLabo();

        if ($equipe instanceof Equipe && $labo !== $equipe->getLabo()) {
            throw new AccessDeniedException("Vous ne pouvez pas modifier une équipe d'un autre laboratoire");
        }

        if(null === $equipe){
            $equipe = new Equipe();
            $equipe->setLabo($labo);
        }

        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $em->persist($equipe);
            $em->flush();

            $this->addFlash('success', "L'équipe a été mis à jour.");

            return $this->redirectToRoute('lab_app_fo_admin_equipes');
        }

        return $this->render('lab_app/equipe/post_edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }
}
