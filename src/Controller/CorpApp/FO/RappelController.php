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
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function list(RappelRepository $rappelRepository)
    {
        $remindedRappels = $rappelRepository->findBy(['user' => $this->userContext->getUser(), 'isReminded' => true],['rappelDate' => 'DESC']);
        $notRemindedRappels = $rappelRepository->findBy(['user' => $this->userContext->getUser(), 'isReminded' => false],['rappelDate' => 'DESC']);

        return $this->render('corp_app/rappel/list.html.twig', [
            'remindedRappels' => $remindedRappels,
            'notRemindedRappels' => $notRemindedRappels
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
        $update = true;
        if (null === $rappel){
            $rappel = new Rappel();
            $rappel->setUser($userContext->getUser());
            $update = false;
        } elseif ($rappel->getUser() !== $userContext->getUser()){
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(RappelType::class, $rappel);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rappel);
            $em->flush();

            $this->addFlash('success', sprintf('Votre rappel a été ' . ($update ? 'modifié' : 'créé') . ' avec succès.'));

            return $this->redirectToRoute('corp_app_fo_rappel_list');
        }

        return $this->render('corp_app/rappel/post_edit.html.twig', [
            'form' => $form->createView(),
            'rappel' => $rappel
        ]);
    }

    /**
     * @Route("/supprimer/{rappelId}", name="corp_app_fo_rappel_delete")
     *
     * @ParamConverter("rappel", options={"id" = "rappelId"})
     */
    public function delete(
        Rappel $rappel,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        UserContext $userContext
    ): Response {
        if ($rappel->getUser() !== $userContext->getUser()){
            throw $this->createAccessDeniedException();
        }
        $rappelTitre = $rappel->getTitre();

        $em->remove($rappel);
        $em->flush();

        $this->addFlash('success', $translator->trans(
            'Le rappel "{titre_rappel}" a été supprimé.',
            [
                'titre_rappel' => $rappelTitre,
            ]
        ));

        return $this->redirectToRoute('corp_app_fo_rappel_list');
    }
}
