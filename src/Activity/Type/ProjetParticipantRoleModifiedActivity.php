<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjetParticipantRoleModifiedActivity implements ActivityInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        EntityLinkService $entityLinkService,
        UserContext $userContext,
        TranslatorInterface $translator
    )
    {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    public static function getType(): string
    {
        return 'projet_participant_role_modified';
    }

    public static function getFilterType(): string
    {
        return 'projet';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'participant',
            'modifiedBy',
            'oldRole',
            'newRole',
            'projet',
        ]);

        $resolver->setAllowedTypes('participant', 'integer');
        $resolver->setAllowedTypes('modifiedBy', 'integer');
        $resolver->setAllowedTypes('oldRole', 'string');
        $resolver->setAllowedTypes('newRole', 'string');
        $resolver->setAllowedTypes('projet', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        if ($activityParameters['modifiedBy'] === $activityParameters['participant']){
            return sprintf(
                "%s %s a modifié son rôle de %s à %s, sur le projet %s.",
                '<i class="fa fa-refresh" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
                $this->translator->trans($activityParameters['oldRole']),
                $this->translator->trans($activityParameters['newRole']),
                $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            );
        } else {
            return sprintf(
                "%s %s a modifié le rôle de %s de %s à %s, sur le projet %s.",
                '<i class="fa fa-refresh" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['participant']),
                $this->translator->trans($activityParameters['oldRole']),
                $this->translator->trans($activityParameters['newRole']),
                $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            );
        }

    }

    public function postUpdate(ProjetParticipant $projetParticipant, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($projetParticipant);

        if (!isset($changes['role'])) {
            return;
        }

        $participant = $projetParticipant->getSocieteUser();
        $modifiedBy = $this->userContext->getSocieteUser();
        $projet = $projetParticipant->getProjet();
        $oldRole = $changes['role'][0];
        $newRole = $changes['role'][1];

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'participant' => intval($participant->getId()),
                'modifiedBy' => intval($modifiedBy->getId()),
                'projet' => intval($projet->getId()),
                'oldRole' => $oldRole,
                'newRole' => $newRole,
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
        $this->em->flush();
    }
}
