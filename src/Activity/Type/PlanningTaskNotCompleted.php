<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;
use App\Entity\SocieteUserNotification;
use App\Notification\Event\PlanningTaskNotCompletedNotification;
use App\Service\EntityLink\EntityLinkService;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
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
        return 'planning_task_not_completed';
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s La date d'échéance de la tâche %s du projet %s est dans 3 jours.",
            '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(ProjetPlanningTask::class, $activityParameters['projetPlanningTask']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projetPlanningTask',
            'projet',
        ]);

        $resolver->setAllowedTypes('projetPlanningTask', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlanningTaskNotCompletedNotification::class => 'onNotification',
        ];
    }

    public function onNotification(PlanningTaskNotCompletedNotification $event): void
    {
        if (!ProductPrivilegeCheker::checkProductPrivilege($event->getSociete(),ProductPrivileges::NOTIFICATION_PLANIFICATION_PROJET)){
            return;
        }

        $projetPlanningTask = $event->getProjetPlanningTask();
        $projet = $event->getProjet();

        $activity = new Activity();

        $activity
            ->setType($this->getType())
            ->setParameters([
                'projetPlanningTask' => $projetPlanningTask->getId(),
                'projet' => $projet->getId(),
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
