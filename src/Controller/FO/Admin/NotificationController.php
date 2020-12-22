<?php

namespace App\Controller\FO\Admin;

use App\Form\SocieteNotificationsType;
use App\Service\SocieteNotificationsService;
use Cron\CronBundle\Entity\CronJob;
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
     * @Route("", name="app_fo_admin_notification")
     */
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SocieteNotificationsService $societeNotificationsService
    ) {
        $societe = $this->getUser()->getSociete();
        $societeNotifications = $societeNotificationsService->loadForSociete($societe);

        $form = $this->createForm(SocieteNotificationsType::class, $societeNotifications);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societeNotificationsService->persistAll($societe, $societeNotifications);
            $em->flush();

            $this->addFlash('success', 'Vos préférences de notifications ont été mises à jour.');

            return $this->redirectToRoute('app_fo_admin_notification');
        }

        return $this->render('notification/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/rapport/{id}", name="app_fo_admin_notification_rapport")
     */
    public function report(CronJob $cronJob)
    {
        $tokens = explode('-', $cronJob->getName());
        $societeId = intval(array_pop($tokens));

        if ($societeId !== $this->getUser()->getSociete()->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('notification/rapport.html.twig', [
            'cronJob' => $cronJob,
        ]);
    }
}
