<?php

namespace App\Onboarding\Step;

use App\Entity\SocieteUser;
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
        return 'Génération de feuilles de temps';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, SocieteUser $societeUser): ?string
    {
        return $urlGenerator->generate(
            'corp_app_fo_admin_timesheet_generate',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isImportant(): bool
    {
        return false;
    }

    public function isCompleted(SocieteUser $societeUser): bool
    {
        if (!$societeUser->hasUser()) {
            return false;
        }

        return $societeUser->getUser()->getOnboardingTimesheetCompleted();
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
        $event->getSocieteUser()->getUser()->setOnboardingTimesheetCompleted(true);

        $this->em->flush();
    }
}
