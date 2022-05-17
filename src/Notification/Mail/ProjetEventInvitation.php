<?php

namespace App\Notification\Mail;

use App\Service\ProjetEvent\IcsFileGenerator;
use Eluceo\iCal\Presentation\Component;
use App\Entity\ProjetEvent;
use App\MultiSociete\UserContext;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

class ProjetEventInvitation
{
    private MailerInterface $mailer;

    private UserContext $userContext;

    private IcsFileGenerator $icsFileGenerator;

    public function __construct(
        MailerInterface $mailer,
        UserContext $userContext,
        IcsFileGenerator $icsFileGenerator
    )
    {
        $this->mailer = $mailer;
        $this->userContext = $userContext;
        $this->icsFileGenerator = $icsFileGenerator;
    }

    public function postPersist(ProjetEvent $projetEvent, LifecycleEventArgs $args): void
    {
        $calendar = $this->icsFileGenerator->generateIcsCalendar($projetEvent);
        $this->sendEmail($projetEvent, $calendar);
    }

    public function postUpdate(ProjetEvent $projetEvent, LifecycleEventArgs $args): void
    {
        $calendar = $this->icsFileGenerator->generateIcsCalendar($projetEvent);
        $this->sendEmail($projetEvent, $calendar, true);
    }

    public function preRemove(ProjetEvent $projetEvent, LifecycleEventArgs $args): void
    {
        $email = (new TemplatedEmail())
            ->subject("[". $projetEvent->getType() ."] Annulation : " . $projetEvent->getText())
            ->htmlTemplate('mail/projet_event_cancel_invitation.html.twig')
            ->textTemplate('mail/projet_event_cancel_invitation.txt.twig')
            ->context([
                'societe' => $projetEvent->getSociete(),
                'projetEvent' => $projetEvent,
            ])
        ;

        foreach ($projetEvent->getProjetEventParticipants() as $projetEventParticipant){
            try{
                $this->mailer->send($email->to($projetEventParticipant->getParticipant()->getSocieteUser()->getUser()->getEmail()));
            } catch (TransportException $e){}
        }
    }

    private function sendEmail(ProjetEvent $projetEvent, string $calendar = null, bool $edit = false): void
    {
        $email = (new TemplatedEmail())
            ->subject("[". ($edit ? "Update | " : "New | ") . $projetEvent->getType() ."] Invitation : " . $projetEvent->getText())
            ->htmlTemplate('mail/projet_event_send_invitation.html.twig')
            ->textTemplate('mail/projet_event_send_invitation.txt.twig')
            ->context([
                'societe' => $projetEvent->getSociete(),
                'projetEvent' => $projetEvent,
            ])
        ;

        if ($calendar !== null){
            $email->attach($calendar, 'rdi_manager_event.ics','text/calendar');
        }

        foreach ($projetEvent->getProjetEventParticipants() as $projetEventParticipant){
            try{
                $this->mailer->send($email->to($projetEventParticipant->getParticipant()->getSocieteUser()->getUser()->getEmail()));
            } catch (TransportException $e){}
        }
    }
}
