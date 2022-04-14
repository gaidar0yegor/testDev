<?php

namespace App\Notification\Mail;

use App\Notification\Event\PlanningTaskNotCompletedNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlanningTaskNotCompleted
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
        $projetPlanningTask = $event->getProjetPlanningTask();
        $projet = $event->getProjet();

        $email = (new TemplatedEmail())
            ->subject('Date d\'échéance d\'une tâche est dans 3 jours')
            ->textTemplate('mail/notification_planning_task_not_completed.txt.twig')
            ->htmlTemplate('mail/notification_planning_task_not_completed.html.twig')
            ->context([
                'projet' => $projet,
                'projetPlanningTask' => $projetPlanningTask,
            ]);

        $toEmails[] = $projet->getChefDeProjet()->getUser()->getEmail();

        foreach ($projetPlanningTask->getParticipants() as $participant){
            if(!in_array($participant->getSocieteUser()->getUser()->getEmail(), $toEmails, true)){
                array_push($toEmails, $participant->getSocieteUser()->getUser()->getEmail());
            }
        }

        foreach ($toEmails as $toEmail){
            $this->mailer->send($email->to($toEmail));
        }
    }
}
