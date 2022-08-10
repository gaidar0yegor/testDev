<?php

namespace App\Notification\Mail;

use App\Notification\Event\PlanningTaskNotCompletedNotification;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlanningTaskNotCompleted implements EventSubscriberInterface
{
    private TranslatorInterface $translator;

    private MailerInterface $mailer;

    public function __construct(
        TranslatorInterface $translator,
        MailerInterface $mailer
    ) {
        $this->translator = $translator;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlanningTaskNotCompletedNotification::class => 'onNotification',
        ];
    }

    public function onNotification(PlanningTaskNotCompletedNotification $event): void
    {
        if (!ProductPrivilegeCheker::checkProductPrivilege($event->getSociete(),ProductPrivileges::PLANIFICATION_PROJET_AVANCE)){
            return;
        }

        $projetPlanningTask = $event->getProjetPlanningTask();
        $projet = $event->getProjet();

        $email = (new TemplatedEmail())
            ->subject('Date d\'échéance d\'une tâche est dans 3 jours')
            ->textTemplate('corp_app/mail/notification_planning_task_not_completed.txt.twig')
            ->htmlTemplate('corp_app/mail/notification_planning_task_not_completed.html.twig')
            ->context([
                'projet' => $projet,
                'projetPlanningTask' => $projetPlanningTask,
            ]);

        $toEmails = [];

        if ($projet->getChefDeProjet()->getUser()->getNotificationPlanningTaskNotCompletedEnabled()){
            $toEmails[] = $projet->getChefDeProjet()->getUser()->getEmail();
        }

        foreach ($projetPlanningTask->getParticipants() as $participant){
            if(
                $participant->getSocieteUser()->getUser()->getNotificationPlanningTaskNotCompletedEnabled() &&
                !in_array($participant->getSocieteUser()->getUser()->getEmail(), $toEmails, true)
            ){
                array_push($toEmails, $participant->getSocieteUser()->getUser()->getEmail());
            }
        }

        foreach ($toEmails as $toEmail){
            $this->mailer->send($email->to($toEmail));
        }
    }
}
