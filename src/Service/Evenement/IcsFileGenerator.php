<?php


namespace App\Service\Evenement;

use App\Entity\Evenement;
use Eluceo\iCal\Domain\Entity\Attendee;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Enum\CalendarUserType;
use Eluceo\iCal\Domain\Enum\ParticipationStatus;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
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

    public function generateIcsCalendar(Evenement $evenement): string
    {
        $myEventUid = 'event_' . $evenement->getId() . uniqid();
        $uniqueIdentifier = new UniqueIdentifier($myEventUid);

        $icalEvent = (new Event($uniqueIdentifier));

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant) {
            $participantUser = $evenementParticipant->getSocieteUser()->getUser();
            $attendee = new Attendee(new EmailAddress($participantUser->getEmail()));
            $attendee->setCalendarUserType(CalendarUserType::INDIVIDUAL())
                ->addMember(new Member(new EmailAddress($participantUser->getEmail())))
                ->setParticipationStatus(
                    $evenementParticipant->getRequired() ? ParticipationStatus::ACCEPTED() : ParticipationStatus::NEEDS_ACTION()
                )
                ->setResponseNeededFromAttendee(!$evenementParticipant->getRequired())
                ->addSentBy(new EmailAddress($evenement->getCreatedBy()->getUser()->getEmail()))
                ->setDisplayName($participantUser->getFullname());

            $icalEvent->addAttendee($attendee);
        }

        $icalEvent
            ->setOrganizer(new Organizer(
                new EmailAddress($evenement->getCreatedBy()->getUser()->getEmail()),
                $evenement->getCreatedBy()->getUser()->getFullname()
            ))
            ->setSummary($evenement->getText())
            ->setDescription($evenement->getDescription() ?? '' )
            ->setLocation(new Location($evenement->getLocation() ?? ''))
            ->setUrl(new Uri($this->urlGenerator->generate('corp_app_fo_current_user_events', [
                'event' => $evenement->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)))
            ->setOccurrence(
                new TimeSpan(
                    new DateTime($evenement->getStartDate(), false),
                    new DateTime($evenement->getEndDate(), false)
                )
            );

        $calendar = new Calendar([$icalEvent]);

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        return str_replace("%40","@",(string)$calendarComponent);
    }
}