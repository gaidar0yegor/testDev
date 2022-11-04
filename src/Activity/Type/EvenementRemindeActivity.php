<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Evenement;
use App\Entity\SocieteUserEvenementNotification;
use App\Notification\Mail\EvenementInvitation;
use App\Service\EntityLink\EntityLinkService;
use App\Twig\DiffDateTimesExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementRemindeActivity implements ActivityInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private EvenementInvitation $mailEvenementInvitation;

    private DiffDateTimesExtension $diffDateTimesExtension;

    public function __construct(
        EntityLinkService $entityLinkService,
        EntityManagerInterface $em,
        EvenementInvitation $mailEvenementInvitation,
        DiffDateTimesExtension $diffDateTimesExtension
    )
    {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->mailEvenementInvitation = $mailEvenementInvitation;
        $this->diffDateTimesExtension = $diffDateTimesExtension;
    }

    public static function getType(): string
    {
        return 'evenement_reminde';
    }

    public static function getFilterType(): string
    {
        return 'evenement';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'evenement',
            'projet'
        ]);

        $resolver->setAllowedTypes('evenement', 'integer');
        $resolver->setAllowedTypes('projet', ['integer','string']);
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        $evenement = $this->em->getRepository(Evenement::class)->find($activityParameters['evenement']);

        return sprintf(
            '%s L\'évènement %s est %s %s.',
            '<i class="fa fa-calendar-check-o" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(Evenement::class, $activityParameters['evenement']),
            $evenement->getStartDate() === $evenement->getReminderAt() ? '' : 'dans',
            $this->diffDateTimesExtension->diffDateTimes($evenement->getStartDate(), $evenement->getReminderAt())
        );
    }

    public function createActivity(Evenement $evenement): void
    {
        $projet = $evenement->getProjet();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'evenement' => intval($evenement->getId()),
                'projet' => null !== $projet ? intval($projet->getId()) : '',
            ])
        ;

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant) {
            $this->em->persist(SocieteUserEvenementNotification::create($activity, $evenementParticipant->getSocieteUser()));
        }

        $this->em->persist($activity);
        $this->em->flush();
    }
}
