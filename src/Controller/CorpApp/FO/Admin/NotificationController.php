<?php

namespace App\Controller\CorpApp\FO\Admin;

use App\Form\SocieteNotificationsType;
use App\Service\SocieteNotificationsService;
use App\MultiSociete\UserContext;
use App\Slack\Slack;
use Cron\CronBundle\Entity\CronJob;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notifications")
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("", name="corp_app_fo_admin_notification")
     */
    public function index(
        Request $request,
        Slack $slack,
        EntityManagerInterface $em,
        UserContext $userContext,
        SocieteNotificationsService $societeNotificationsService
    ) {
        $societe = $userContext->getSocieteUser()->getSociete();
        $societeNotifications = $societeNotificationsService->loadForSociete($societe);

        $form = $this->createForm(SocieteNotificationsType::class, $societeNotifications);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societeNotificationsService->persistAll($societe, $societeNotifications);
            $em->flush();

            $this->addFlash('success', 'Vos préférences de notifications ont été mises à jour.');

            return $this->redirectToRoute('corp_app_fo_admin_notification');
        }

        return $this->render('corp_app/notification/index.html.twig', [
            'form' => $form->createView(),
            'slackRedirectUri' => $slack->generateRedirectUri(),
        ]);
    }

    /**
     * @Route("/rapport/{id}", name="corp_app_fo_admin_notification_rapport")
     */
    public function report(CronJob $cronJob, UserContext $userContext)
    {
        $tokens = explode('-', $cronJob->getName());
        $societeId = intval(array_pop($tokens));

        if ($societeId !== $userContext->getSocieteUser()->getSociete()->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('corp_app/notification/rapport.html.twig', [
            'cronJob' => $cronJob,
            'cronReports' => $cronJob->getReports()->matching(new Criteria(null, ['runAt' => 'desc'], null, 20)),
        ]);
    }
}
