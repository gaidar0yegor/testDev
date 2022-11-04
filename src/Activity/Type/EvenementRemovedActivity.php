<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserEvenementNotification;
use App\Notification\Event\EvenementRemovedEvent;
use App\Notification\Mail\EvenementInvitation;
use App\Service\EntityLink\EntityLinkGenerator\EvenementLink;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementRemovedActivity implements ActivityInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    private EvenementInvitation $mailEvenementInvitation;

    public function __construct(
        EntityLinkService $entityLinkService,
        UserContext $userContext,
        EntityManagerInterface $em,
        EvenementInvitation $mailEvenementInvitation
    )
    {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
        $this->mailEvenementInvitation = $mailEvenementInvitation;
    }

    public static function getType(): string
    {
        return 'evenement_removed';
    }

    public static function getFilterType(): string
    {
        return 'evenement';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'evenement',
            'projet',
            'removedBy'
        ]);

        $resolver->setAllowedTypes('evenement', 'string');
        $resolver->setAllowedTypes('projet', ['integer','string']);
        $resolver->setAllowedTypes('removedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        $textProjet = $activityParameters['projet']
            ? ' sur le projet ' . $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            : '';

        return sprintf(
            '%s %s a annulé l\'évènement %s%s.',
            '<i class="fa fa-calendar" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['removedBy']),
            $activityParameters['evenement'],
            $textProjet
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvenementRemovedEvent::class => 'createActivity',
        ];
    }

    public function createActivity(EvenementRemovedEvent $event): void
    {
        $evenement = $event->getEvenement();
        $projet = $event->getProjet();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'evenement' => mb_strlen($evenement->getText()) <= EvenementLink::MAX_SIZE ? $evenement->getText() : mb_substr($evenement->getText(), 0, EvenementLink::MAX_SIZE).'…',
                'projet' => null !== $projet ? intval($projet->getId()) : '',
                'removedBy' => intval($this->userContext->getSocieteUser()->getId()),
            ])
        ;

        if (null !== $projet){
            $projetActivity = new ProjetActivity();
            $projetActivity
                ->setProjet($evenement->getProjet())
                ->setActivity($activity)
            ;
            $this->em->persist($projetActivity);
        }

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant) {
            if ($evenementParticipant->getSocieteUser() !== $this->userContext->getSocieteUser()){
                $this->em->persist(SocieteUserEvenementNotification::create($activity, $evenementParticipant->getSocieteUser()));
            }
        }

        $this->em->persist($activity);

        $this->mailEvenementInvitation->sendMailPreRemove($evenement);

        $this->em->flush();
    }
}
