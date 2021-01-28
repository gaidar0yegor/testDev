<?php

namespace App\Onboarding\Step;

use App\Entity\User;
use App\Onboarding\OnboardingStepInterface;
use App\Service\Timesheet\Event\TimesheetEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GenerateTimesheetStep implements OnboardingStepInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getText(): string
    {
        return 'Générez les feuilles de temps';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, User $user): ?string
    {
        return $urlGenerator->generate(
            'app_fo_admin_timesheet_generate',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isCompleted(User $user): bool
    {
        return $user->getOnboardingTimesheetCompleted();
    }

    public static function getPriority(): int
    {
        return 10;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TimesheetEvent::GENERATED => 'timesheetGenerated',
        ];
    }

    public function timesheetGenerated(TimesheetEvent $event): void
    {
        $event->getUser()->setOnboardingTimesheetCompleted(true);

        $this->em->flush();
    }
}
