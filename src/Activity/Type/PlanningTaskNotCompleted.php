<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;
use App\Entity\SocieteUserNotification;
use App\Notification\Event\PlanningTaskNotCompletedNotification;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningTaskNotCompleted implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    public function __construct(
        EntityLinkService $entityLinkService,
        EntityManagerInterface $em
    ) {
        $this->entityLinkService = $entityLinkService;
        $this->em = $em;
    }

    public static function getType(): string
    {
        return 'projet_planning_task_not_completed';
    }

    public static function getFilterType(): string
    {
        return 'projet_planning';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projetPlanningTask',
            'projet',
            'nbrDaysNotifTaskEcheance',
        ]);

        $resolver->setAllowedTypes('projetPlanningTask', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('nbrDaysNotifTaskEcheance', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s La date d'échéance de la tâche %s du projet %s est dans %s jours.",
            '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(ProjetPlanningTask::class, $activityParameters['projetPlanningTask']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet']),
            (isset($activityParameters['nbrDaysNotifTaskEcheance']) ? $activityParameters['nbrDaysNotifTaskEcheance'] : 3),
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlanningTaskNotCompletedNotification::class => 'onNotification',
        ];
    }

    public function onNotification(PlanningTaskNotCompletedNotification $event): void
    {
        $projetPlanningTask = $event->getProjetPlanningTask();
        $projet = $event->getProjet();

        $activity = new Activity();

        $activity
            ->setType(self::getType())
            ->setParameters([
                'projetPlanningTask' => $projetPlanningTask->getId(),
                'projet' => $projet->getId(),
                'nbrDaysNotifTaskEcheance' => $projet->getNbrDaysNotifTaskEcheance(),
            ])
        ;

        $this->em->persist($activity);

        $chefDeProjet = $projet->getChefDeProjet();
        $userNotification = SocieteUserNotification::create($activity, $chefDeProjet);
        $this->em->persist($userNotification);

        foreach ($projetPlanningTask->getParticipants() as $participant){
            if ($participant->getSocieteUser() !== $chefDeProjet){
                $userNotification = SocieteUserNotification::create($activity, $participant->getSocieteUser());
                $this->em->persist($userNotification);
            }
        }

        $this->em->flush();
    }
}
