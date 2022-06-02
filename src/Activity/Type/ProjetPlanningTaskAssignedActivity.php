<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetPlanningTask;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Notification\Event\ProjetParticipantTaskAssignedEvent;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjetPlanningTaskAssignedActivity implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    private UrlGeneratorInterface $urlGenerator;

    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        EntityLinkService $entityLinkService,
        UserContext $userContext,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public static function getType(): string
    {
        return 'projet_planning_task_assigned';
    }

    public static function getFilterType(): string
    {
        return 'projet_planning';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'participant',
            'projetPlanningTask',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('participant', 'integer');
        $resolver->setAllowedTypes('projetPlanningTask', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s est affecté à la tâche %s du projet %s.",
            '<i class="fa fa-tasks" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['participant']),
            $this->entityLinkService->generateLink(ProjetPlanningTask::class, $activityParameters['projetPlanningTask']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
         );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjetParticipantTaskAssignedEvent::class => 'createActivity',
        ];
    }

    public function createActivity(ProjetParticipantTaskAssignedEvent $event): void
    {
        $projet = $event->getProjet();
        $participant = $event->getSocieteUser();
        $projetPlanningTask = $event->getProjetPlanningTask();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($projet->getId()),
                'participant' => intval($participant->getId()),
                'projetPlanningTask' => intval($projetPlanningTask->getId()),
            ])
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setActivity($activity)
            ->setProjet($projet)
        ;

        $societeUserNotification = SocieteUserNotification::create($activity,$participant);

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($participant)
            ->setActivity($activity)
        ;

        $this->em->persist($activity);
        $this->em->persist($projetActivity);
        $this->em->persist($societeUserActivity);
        $this->em->persist($societeUserNotification);
    }
}
