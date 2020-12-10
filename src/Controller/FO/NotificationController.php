<?php

namespace App\Controller\FO;

use App\DTO\SocieteNotifications;
use App\Form\SocieteNotificationsType;
use App\Repository\CronJobRepository;
use App\Service\SocieteNotificationsService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notifications")
 * @IsGranted("ROLE_FO_ADMIN")
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/", name="app_fo_notification")
     */
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SocieteNotificationsService $societeNotificationsService
    ) {
        $societeNotifications = $societeNotificationsService->loadForSociete($this->getUser());

        $form = $this->createForm(SocieteNotificationsType::class, $societeNotifications);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societeNotificationsService->persistAll($societeNotifications);
            $em->flush();

            $this->addFlash('success', 'Vos préférences de notifications ont été mises à jour.');

            return $this->redirectToRoute('app_fo_notification');
        }

        return $this->render('notification/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
