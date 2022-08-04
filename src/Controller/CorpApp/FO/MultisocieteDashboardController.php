<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\DashboardConsolide;
use App\Form\DashboardConsolideType;
use App\MultiSociete\UserContext;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Voter\HasProductPrivilegeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/mes-societes/dashboard")
 */
class MultisocieteDashboardController extends AbstractController
{
    /**
     * @Route("/general", name="corp_app_fo_multi_societe_dashboard_general")
     */
    public function dashboardGeneral(
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $userContext->disconnectSociete();
        $em->flush();

        return $this->render('corp_app/multi_societe/dashboard-general.html.twig',[
            'societeUsers' => $userContext->getUser()->getSocieteUsers()
        ]);
    }

    /**
     * @Route(
     *     "/consolide/{id}",
     *     defaults={"id"=null},
     *     requirements={"id"="\d+"},
     *     name="corp_app_fo_multi_societe_dashboard_consolide",
     *     methods={"GET"}
     *     )
     */
    public function dashboardConsolide(
        DashboardConsolide $dashboardConsolide = null,
        UserContext $userContext,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);

        $userContext->disconnectSociete();
        $em->flush();

        return $this->render('corp_app/multi_societe/dashboard_consolide/show.html.twig',[
            'dashboardConsolide' => $dashboardConsolide,
            'societeUsers' => $dashboardConsolide ? $dashboardConsolide->getSocieteUsers() : $userContext->getUser()->getSocieteUsers(),
        ]);
    }

    /**
     * @Route(
     *     "/consolide/ajouter/{id}",
     *     defaults={"id"=null},
     *     requirements={"id"="\d+"},
     *     name="corp_app_fo_multi_societe_dashboard_consolide_ajouter",
     *     methods={"GET","POST"}
     *     )
     */
    public function new(
        DashboardConsolide $dashboardConsolide = null,
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UserContext $userContext
    ): Response {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::MULTI_SOCIETE_DASHBOARD);
        
        if (null === $dashboardConsolide){
            $dashboardConsolide = new DashboardConsolide();
            $dashboardConsolide
                ->setUser($userContext->getUser())
                ->setCreatedAt(new \DateTime())
            ;
        }

        $form = $this->createForm(DashboardConsolideType::class, $dashboardConsolide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($dashboardConsolide);
            $em->flush();

            $this->addFlash('success', $translator->trans(
                'Le tableau de bord "{titre_dashboard_consolide}" a été créé avec succès.',
                [
                    'titre_dashboard_consolide' => $dashboardConsolide->getTitle(),
                ]
            ));

            return $this->redirectToRoute('corp_app_fo_multi_societe_dashboard_consolide', [
                'id' => $dashboardConsolide->getId(),
            ]);
        }

        return $this->render('corp_app/multi_societe/dashboard_consolide/new.html.twig', [
            'dashboardConsolide' => $dashboardConsolide,
            'form' => $form->createView(),
        ]);
    }
}
