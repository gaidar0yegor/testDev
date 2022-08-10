<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\SocieteUserNotification;
use App\Notification\Event\RappelSaisieTempsNotification;
use App\Repository\SocieteUserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use IntlDateFormatter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SaisieTemps implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private SocieteUserRepository $societeUserRepository;

    private UrlGeneratorInterface $urlGenerator;

    private IntlDateFormatter $formatter;

    public function __construct(
        EntityManagerInterface $em,
        SocieteUserRepository $societeUserRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->em = $em;
        $this->societeUserRepository = $societeUserRepository;
        $this->urlGenerator = $urlGenerator;

        $this->formatter = datefmt_create(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            null,
            null,
            'MMMM'
        );
    }

    public static function getType(): string
    {
        return 'saisie_temps';
    }

    public static function getFilterType(): string
    {
        return 'saisie_temps';
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        $month = $this->formatter->format(DateTime::createFromFormat('Y-m', $activityParameters['month']));
        $yearMonth = explode('-', $activityParameters['month']);
        $link = $this->urlGenerator->generate(
            'corp_app_fo_temps',
            [
                'year' => $yearMonth[0],
                'month' => $yearMonth[1],
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return sprintf(
            '%s <a href="%s">Vous devriez saisir vos temps de %s&nbsp;!</a>',
            '<i class="fa fa-calendar" aria-hidden="true"></i>',
            $link,
            $month
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'month',
        ]);

        $resolver->setAllowedTypes('month', 'string');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RappelSaisieTempsNotification::class => 'onNotification',
        ];
    }

    public function onNotification(RappelSaisieTempsNotification $event): void
    {
        $societe = $event->getSociete();
        $month = $event->getMonth();
        $activity = new Activity();

        $activity
            ->setType(self::getType())
            ->setParameters([
                'month' => $month->format('Y-m'),
            ])
        ;

        $users = $this->societeUserRepository->findBy([
            'societe' => $societe,
        ]);

        foreach ($users as $user) {
            $userNotification = SocieteUserNotification::create($activity, $user);

            $this->em->persist($userNotification);
        }

        $this->em->persist($activity);
        $this->em->flush();
    }
}
