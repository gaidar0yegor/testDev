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
use Symfony\Contracts\Translation\TranslatorInterface;

class EvenementInvitation
{
    private MailerInterface $mailer;

    private UserContext $userContext;

    private TranslatorInterface $translator;

    private IcsFileGenerator $icsFileGenerator;

    public function __construct(
        MailerInterface $mailer,
        UserContext $userContext,
        TranslatorInterface $translator,
        IcsFileGenerator $icsFileGenerator
    )
    {
        $this->mailer = $mailer;
        $this->userContext = $userContext;
        $this->translator = $translator;
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

    private function sendEmail(Evenement $evenement, string $calendar = null, bool $edit = false): void
    {
        if ($edit){
            $email = (new TemplatedEmail())
                ->subject("[Update | " . $this->translator->trans($evenement->getType()) ."] Invitation : " . $evenement->getText())
                ->htmlTemplate('corp_app/mail/evenement_update_invitation.html.twig')
                ->textTemplate('corp_app/mail/evenement_update_invitation.txt.twig')
                ->context([
                    'societe' => $evenement->getSociete(),
                    'evenement' => $evenement,
                ])
            ;
        } else {
            $email = (new TemplatedEmail())
                ->subject("[New | " . $this->translator->trans($evenement->getType()) ."] Invitation : " . $evenement->getText())
                ->htmlTemplate('corp_app/mail/evenement_send_invitation.html.twig')
                ->textTemplate('corp_app/mail/evenement_send_invitation.txt.twig')
                ->context([
                    'societe' => $evenement->getSociete(),
                    'evenement' => $evenement,
                ])
            ;
        }


        if ($calendar !== null){
            $email->attach($calendar, 'rdi_manager_event.ics','text/calendar');
        }

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant){
            try{
                if ($evenementParticipant->getSocieteUser()->getUser()->getNotificationEvenementInvitationEnabled()){
                    $this->mailer->send($email->to($evenementParticipant->getSocieteUser()->getUser()->getEmail()));
                }
            } catch (TransportException $e){}
        }

        foreach ($evenement->getExternalParticipantEmails() as $externalParticipantEmail){
            try{
                $this->mailer->send($email->to($externalParticipantEmail));
            } catch (TransportException $e){}
        }
    }

    public function sendMailPreRemove(Evenement $evenement): void
    {
        $email = (new TemplatedEmail())
            ->subject("[Annulation | " . $this->translator->trans($evenement->getType()) ."] Invitation : " . $evenement->getText())
            ->htmlTemplate('corp_app/mail/evenement_cancel_invitation.html.twig')
            ->textTemplate('corp_app/mail/evenement_cancel_invitation.txt.twig')
            ->context([
                'societe' => $evenement->getSociete(),
                'evenement' => $evenement,
            ])
        ;

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant){
            try{
                if ($evenementParticipant->getSocieteUser()->getUser()->getNotificationEvenementInvitationEnabled()){
                    $this->mailer->send($email->to($evenementParticipant->getSocieteUser()->getUser()->getEmail()));
                }
            } catch (TransportException $e){}
        }

        foreach ($evenement->getExternalParticipantEmails() as $externalParticipantEmail){
            try{
                $this->mailer->send($email->to($externalParticipantEmail));
            } catch (TransportException $e){}
        }
    }
}
