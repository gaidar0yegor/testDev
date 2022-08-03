<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\Projet;
use App\Entity\ProjetSuspendPeriod;
use App\Form\FaitMarquantType;
use App\Form\ProjetFormType;
use App\Entity\ProjetParticipant;
use App\ProjetResourceInterface;
use App\Repository\ProjetActivityRepository;
use App\Repository\ProjetRepository;
use App\Service\FaitMarquantService;
use App\Service\StatisticsService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use App\DTO\ProjetExportParameters;
use App\Form\ProjetExportType;
use App\Form\SocieteUsersSelectionType;
use App\Security\Role\RoleProjet;
use App\MultiSociete\UserContext;
use App\Notification\Event\AddedAsContributorNotification;
use App\Service\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/projets")
 */
class ProjetController extends AbstractController
{
    private Pdf $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @Route("", name="corp_app_fo_projets")
     */
    public function listerProjet(UserContext $userContext, ProjetRepository $projetRepository)
    {
        $projets = $projetRepository->findAllForUserInYear($userContext->getSocieteUser());
        $yearRange = $projetRepository->findProjetsYearRangeFor($userContext->getSocieteUser());

        return $this->render('corp_app/projets/liste_projets.html.twig', [
            'projets'=> $projets,
            'yearMin' => $yearRange['yearMin'] ?? date('Y'),
            'yearMax' => $yearRange['yearMax'] ?? date('Y'),
        ]);
    }

    /**
     * @Route("/creation", name="corp_app_fo_projet_creation")
     *
     * @IsGranted("SOCIETE_CDP")
     */
    public function creation(Request $request, UserContext $userContext) : Response
    {
        $projet = new Projet();
        $participant = new ProjetParticipant();
        $participant
            ->setSocieteUser($userContext->getSocieteUser())
            ->setRole(RoleProjet::CDP)
        ;

        $projet
            ->setSociete($userContext->getSocieteUser()->getSociete())
            ->addProjetParticipant($participant)
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->modify('+2 years'))
        ;

        $form = $this->createForm(ProjetFormType::class, $projet);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $societe = $projet->getSociete();
            $usedColors = $societe->getUsedProjectColors();
            $usedColors = $usedColors ? (in_array($projet->getColorCode(),$usedColors) ? $usedColors : array_merge($usedColors,[$projet->getColorCode()])) :[$projet->getColorCode()];
            $societe->setUsedProjectColors($usedColors);

            $em->persist($participant);
            $em->persist($projet);
            $em->persist($societe);
            $em->flush();

            $this->addFlash('success', sprintf('Le projet "%s" a été créé.', $projet->getTitre()));

            return $this->redirectToRoute('corp_app_fo_projet_add_contributors', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/projets/saisie_infos_projet.html.twig', [
            'form' => $form->createView(),
            'bouton' => "Soumettre",
            'attr' => [
                'class' =>'btn btn-success',
            ]
        ]);
    }

    /**
     * @Route("/{id}/add-contributors", name="corp_app_fo_projet_add_contributors")
     */
    public function addFirstContributors(
        Request $request,
        Projet $projet,
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        ParticipantService $participantService,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('edit', $projet);

        $form = $this->createForm(SocieteUsersSelectionType::class, [
            'societeUsers' => $projet
                ->getProjetParticipants()
                ->map(function (ProjetParticipant $projetParticipant) {
                    return $projetParticipant->getSocieteUser();
                })
            ,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societeUsers = $form->getData()['societeUsers'];

            foreach ($societeUsers as $societeUser) {
                if ($participantService->isParticipant($societeUser, $projet)) {
                    continue;
                }

                $em->persist(ProjetParticipant::create($societeUser, $projet, RoleProjet::CONTRIBUTEUR));
            }

            $em->flush();

            foreach ($societeUsers as $societeUser) {
                $dispatcher->dispatch(new AddedAsContributorNotification($societeUser, $projet));
            }

            if (count($societeUsers) > 0) {
                $this->addFlash('success', $translator->trans('n_contributors_added', [
                    'n' => count($societeUsers),
                ]));
            }

            return $this->redirectToRoute('corp_app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/projets/add_contributors.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="corp_app_fo_projet_modifier")
     */
    public function edition(Request $request, Projet $projet): Response
    {
        $this->denyAccessUnlessGranted('edit', $projet);

        $form = $this->createForm(ProjetFormType::class, $projet);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $societe = $projet->getSociete();
            $usedColors = $societe->getUsedProjectColors();
            $usedColors = $usedColors ? (in_array($projet->getColorCode(),$usedColors) ? $usedColors : array_merge($usedColors,[$projet->getColorCode()])) :[$projet->getColorCode()];
            $societe->setUsedProjectColors($usedColors);

            $em->persist($projet);
            $em->persist($societe);
            $em->flush();

            $this->addFlash('success', sprintf('Le projet "%s" a été modifié.', $projet->getTitre()));
            return $this->redirectToRoute('corp_app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/projets/edition_projet.html.twig', [
            'projet' => $projet,
            'bouton' => "Modifier",
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="corp_app_fo_projet", requirements={"id"="\d+"})
     */
    public function ficheProjet(
        UserContext $userContext,
        Projet $projet,
        ParticipantService $participantService
    ) {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('corp_app/projets/fiche_projet.html.twig', [
            'projet' => $projet,
            'faitMarquants' => $projet->getFaitMarquants(),
            'participation' => $participantService->getProjetParticipant($userContext->getSocieteUser(), $projet),
            'nextEvenements' => $projet->getNextEvenements(),
            'contributeurs' => $participantService->getProjetParticipantsWithRoleExactly(
                $projet->getActiveProjetParticipants(),
                RoleProjet::CONTRIBUTEUR
            ),
            'userCanEditProjet' => $this->isGranted('edit', $projet),
            'userCanAddFaitMarquant' => $this->isGranted(ProjetResourceInterface::CREATE, $projet),
        ]);
    }

    /**
     * @Route("/{id}/activite", name="corp_app_fo_projet_activity", requirements={"id"="\d+"})
     */
    public function projetActivity(Projet $projet, ProjetActivityRepository $projetActivityRepository)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('corp_app/projets/projet_activity.html.twig', [
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet),
            'activities' => $projetActivityRepository->findByProjet($projet),
        ]);
    }

    /**
     * @Route("/{id}/activite/modifier", name="corp_app_fo_projet_activity_edit", requirements={"id"="\d+"})
     */
    public function projetActivityEdit(Projet $projet, ProjetActivityRepository $projetActivityRepository)
    {
        $this->denyAccessUnlessGranted('edit', $projet);

        return $this->render('corp_app/projets/projet_activity.html.twig', [
            'edit' => true,
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet),
            'activities' => $projetActivityRepository->findByProjet($projet),
        ]);
    }

    /**
     * @Route("/{id}.pdf", name="corp_app_fo_projet_pdf")
     */
    public function ficheProjetGeneratformatePdf(Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        $sheetHtml = $this->renderView('corp_app/projets/pdf/pdf_fiche_projet.html.twig', [
            'projet' => $projet,
        ]);

        return $this->createPdfResponse($sheetHtml);
    }

    private function createPdfResponse(string $htmlContent, string $filename = 'projet.pdf'): PdfResponse
    {
        $options = [
            'margin-top'    => 15,
            'margin-right'  => 15,
            'margin-bottom' => 15,
            'margin-left'   => 15,
            'images' => true,
        ];
        $result = $this->pdf->getOutputFromHtml($htmlContent, $options);
        return new PdfResponse($result, $filename);
    }

    /**
     * @Route("/{id}/custom", name="corp_app_fo_projet_custom")
     */
    public function ficheProjetGenerateCustom(Projet $projet, Request $request, StatisticsService $statisticsService)
    {
        $this->denyAccessUnlessGranted('view', $projet);
        $customTime = new ProjetExportParameters($projet);

        $form = $this->createForm(ProjetExportType::class, $customTime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customTime = $form->getData();

            if (in_array(ProjetExportParameters::STATISTIQUES, $customTime->getExportOptions()) ){
                $yearDebut = $customTime->getDateDebut() ? (int)$customTime->getDateDebut()->format('Y') : (int)(new \DateTime())->format('Y');
                $yearFin = $customTime->getDateFin() ? (int)$customTime->getDateFin()->format('Y') : (int)(new \DateTime())->format('Y');
                for ($year = $yearDebut ; $year <= $yearFin ; $year++){
                    $customTime->statistics[$year]['percent'] = $statisticsService->getTempsProjetParUsers($projet,$year,'percent');
                    $customTime->statistics[$year]['hour'] = $statisticsService->getTempsProjetParUsers($projet,$year,'hour');
                }
            }

            if ($customTime->getformat() == 'html') {

                return $this->render('corp_app/projets/pdf/pdf_fiche_projet.html.twig', [
                    'customTime' => $customTime,
                    'projet' => $projet,
                ]);
            }

            $sheetHtml = $this->renderView('corp_app/projets/pdf/pdf_fiche_projet.html.twig', [
                'customTime' => $customTime,
                'projet' => $projet,
            ]);
            return $this->createPdfResponse($sheetHtml);
        }
        return $this->render('corp_app/projets/generer_fait_marquant.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet)
        ]);
    }

    /**
     * @Route("/{id}/supprimer", name="corp_app_fo_projet_delete")
     */
    public function delete(
        Projet $projet,
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('delete_project_'.$projet->getId(), $request->get('_token'))) {
                $this->addFlash('danger', $translator->trans('csrf_token_invalid'));

                return $this->redirectToRoute('corp_app_fo_projet_delete', [
                    'id' => $projet->getId(),
                ]);
            }

            $em->remove($projet);
            $em->flush();

            $this->addFlash('warning', $translator->trans('project_have_been_deleted', [
                'projectAcronyme' => $projet->getAcronyme(),
            ]));

            return $this->redirectToRoute('corp_app_fo_projets');
        }

        return $this->render('corp_app/projets/delete.html.twig', [
            'projet' => $projet,
        ]);
    }

    /**
     * @Route("/{id}/suspendre", name="corp_app_fo_projet_suspend")
     */
    public function suspend(
        Projet $projet,
        Request $request,
        TranslatorInterface $translator,
        FaitMarquantService $faitMarquantService,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        if ($projet->getIsSuspended()){
            $this->addFlash('danger', $translator->trans('project_have_been_suspended', [
                'projectAcronyme' => $projet->getAcronyme(),
            ]));

            return $this->redirectToRoute('corp_app_fo_projet', ['id' => $projet->getId()]);
        }

        $faitMarquant = $faitMarquantService->CreateFmOfProjectSuspension($projet);

        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $suspendPeriod = new ProjetSuspendPeriod();
            $suspendPeriod->setSuspendedAt($faitMarquant->getDate());
            $projet->setIsSuspended(true);
            $projet->addProjetSuspendPeriod($suspendPeriod);

            $em->persist($suspendPeriod);
            $em->persist($faitMarquant);
            $em->persist($projet);
            $em->flush();

            $this->addFlash('warning', $translator->trans('project_have_been_suspended', [
                'projectAcronyme' => $projet->getAcronyme(),
            ]));

            return $this->redirectToRoute('corp_app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/projets/suspend.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/reactiver", name="corp_app_fo_projet_resume")
     */
    public function resume(
        Projet $projet,
        Request $request,
        TranslatorInterface $translator,
        FaitMarquantService $faitMarquantService,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('edit', $projet);

        if (!$projet->getIsSuspended()){
            $this->addFlash('danger', $translator->trans('project_have_been_resumed', [
                'projectAcronyme' => $projet->getAcronyme(),
            ]));

            return $this->redirectToRoute('corp_app_fo_projet', ['id' => $projet->getId()]);
        }

        $projet->setIsSuspended(false);

        $faitMarquant = $faitMarquantService->CreateFmOfProjectResume($projet);

        $form = $this->createForm(FaitMarquantType::class, $faitMarquant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $suspendPeriod = $em->getRepository(ProjetSuspendPeriod::class)->findToResume($projet);

            if ($faitMarquant->getDate() < $suspendPeriod->getSuspendedAt()){
                $form->addError(new FormError("
                La date de ré-activation du projet doit être comprise entre " .
                    $suspendPeriod->getSuspendedAt()->format('d M Y') . " et " .
                    (new \DateTime())->format('d M Y'))
                );
                return $this->render('corp_app/projets/suspend.html.twig', [
                    'projet' => $projet,
                    'form' => $form->createView(),
                ]);
            }


            $suspendPeriod->setResumedAt($faitMarquant->getDate());
            $projet->setIsSuspended(false);

            $em->persist($suspendPeriod);
            $em->persist($faitMarquant);
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', $translator->trans('project_have_been_resumed', [
                'projectAcronyme' => $projet->getAcronyme(),
            ]));

            return $this->redirectToRoute('corp_app_fo_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('corp_app/projets/resume.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }
}
