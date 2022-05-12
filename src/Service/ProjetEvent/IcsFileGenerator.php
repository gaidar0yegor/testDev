<?php


namespace App\Service\ProjetEvent;

use App\Entity\ProjetEvent;
use Eluceo\iCal\Domain\Entity\Attendee;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Enum\CalendarUserType;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Member;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IcsFileGenerator
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function generateIcsCalendar(ProjetEvent $projetEvent): Component
    {
        $myEventUid = 'event_' . $projetEvent->getId();
        $uniqueIdentifier = new UniqueIdentifier($myEventUid);

        $icalEvent = (new Event($uniqueIdentifier));

        foreach ($projetEvent->getProjetEventParticipants() as $projetEventParticipant) {
            $participantUser = $projetEventParticipant->getParticipant()->getSocieteUser()->getUser();
            $attendee = new Attendee(new EmailAddress($participantUser->getEmail()));
            $attendee->setCalendarUserType(CalendarUserType::INDIVIDUAL())
                ->addMember(new Member(new EmailAddress($participantUser->getEmail())))
                ->addSentBy(new EmailAddress($projetEvent->getCreatedBy()->getUser()->getEmail()))
                ->setDisplayName($participantUser->getFullname());

            $icalEvent->addAttendee($attendee);
        }

        $icalEvent
            ->setOrganizer(new Organizer(
                new EmailAddress($projetEvent->getCreatedBy()->getUser()->getEmail()),
                $projetEvent->getCreatedBy()->getUser()->getFullname(),
                null,
                new EmailAddress($projetEvent->getCreatedBy()->getUser()->getEmail())
            ))
            ->setSummary($projetEvent->getText())
            ->setDescription($projetEvent->getDescription())
            ->setUrl(new Uri($this->urlGenerator->generate('app_fo_projet_events', [
                'projetId' => $projetEvent->getProjet()->getId(),
                'event' => $projetEvent->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)))
            ->setOccurrence(
                new TimeSpan(
                    new DateTime($projetEvent->getStartDate(), false),
                    new DateTime($projetEvent->getEndDate(), false)
                )
            );

        $calendar = new Calendar([$icalEvent]);

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        return $calendarComponent;
    }
}