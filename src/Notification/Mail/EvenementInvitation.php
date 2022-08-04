<?php

namespace App\Notification\Mail;

use App\Service\Evenement\IcsFileGenerator;
use Eluceo\iCal\Presentation\Component;
use App\Entity\Evenement;
use App\MultiSociete\UserContext;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

class EvenementInvitation
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

    public function postPersist(Evenement $evenement, LifecycleEventArgs $args): void
    {
        $calendar = $this->icsFileGenerator->generateIcsCalendar($evenement);
        $this->sendEmail($evenement, $calendar);
    }

    public function postUpdate(Evenement $evenement, LifecycleEventArgs $args): void
    {
        $calendar = $this->icsFileGenerator->generateIcsCalendar($evenement);
        $this->sendEmail($evenement, $calendar, true);
    }

    public function preRemove(Evenement $evenement, LifecycleEventArgs $args): void
    {
        $email = (new TemplatedEmail())
            ->subject("[". $evenement->getType() ."] Annulation : " . $evenement->getText())
            ->htmlTemplate('corp_app/mail/evenement_cancel_invitation.html.twig')
            ->textTemplate('corp_app/mail/evenement_cancel_invitation.txt.twig')
            ->context([
                'societe' => $evenement->getSociete(),
                'evenement' => $evenement,
            ])
        ;

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant){
            try{
                $this->mailer->send($email->to($evenementParticipant->getSocieteUser()->getUser()->getEmail()));
            } catch (TransportException $e){}
        }
    }

    private function sendEmail(Evenement $evenement, string $calendar = null, bool $edit = false): void
    {
        $email = (new TemplatedEmail())
            ->subject("[". ($edit ? "Update | " : "New | ") . $evenement->getType() ."] Invitation : " . $evenement->getText())
            ->htmlTemplate('corp_app/mail/evenement_send_invitation.html.twig')
            ->textTemplate('corp_app/mail/evenement_send_invitation.txt.twig')
            ->context([
                'societe' => $evenement->getSociete(),
                'evenement' => $evenement,
            ])
        ;

        if ($calendar !== null){
            $email->attach($calendar, 'rdi_manager_event.ics','text/calendar');
        }

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant){
            try{
                $this->mailer->send($email->to($evenementParticipant->getSocieteUser()->getUser()->getEmail()));
            } catch (TransportException $e){}
        }
    }
}
