<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\Rappel;
use App\Form\RappelType;
use App\MultiSociete\UserContext;
use App\Repository\RappelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/rappels")
 */
class RappelController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected UserContext $userContext;

    public function __construct(EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->userContext = $userContext;
    }

    /**
     * @Route(
     *     "/liste",
     *     name="corp_app_fo_rappel_list"
     * )
     */
    public function list(
        RappelRepository $rappelRepository
    )
    {
        $rappels = $rappelRepository->findAll();

        return $this->render('corp_app/rappel/list.html.twig', [
            'rappels' => $rappels
        ]);
    }

    /**
     * @Route("/gestion/{rappelId}", name="corp_app_fo_rappel_post_edit", defaults={"rappelId"=null})
     *
     * @ParamConverter("rappel", options={"id" = "rappelId"})
     */
    public function gestion(
        Request $request,
        UserContext $userContext,
        Rappel $rappel = null
    ) : Response
    {
        if (null === $rappel){
            $rappel = new Rappel();
            $rappel->setUser($userContext->getUser());
        }

        $form = $this->createForm(RappelType::class, $rappel);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rappel);
            $em->flush();

            $this->addFlash('success', sprintf('Votre rappel a été crée avec succès.'));

            return $this->redirectToRoute('corp_app_fo_rappel_list');
        }

        return $this->render('corp_app/rappel/post_edit.html.twig', [
            'form' => $form->createView(),
            'rappel' => $rappel
        ]);
    }
}
