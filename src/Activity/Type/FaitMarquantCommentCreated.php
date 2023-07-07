<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\FaitMarquantComment;
use App\Entity\Projet;
use App\Entity\SocieteUserNotification;
use App\Entity\User;
use App\Service\EntityLink\EntityLinkService;
use App\Service\ParticipantService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaitMarquantCommentCreated implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    private ParticipantService $participantService;

    public function __construct(EntityLinkService $entityLinkService, ParticipantService $participantService)
    {
        $this->entityLinkService = $entityLinkService;
        $this->participantService = $participantService;
    }

    public static function getType(): string
    {
        return 'fait_marquant_comment_created';
    }

    public static function getFilterType(): string
    {
        return 'fait_marquant';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'user',
            'faitMarquant',
            'projet',
        ]);

        $resolver->setAllowedTypes('user', 'integer');
        $resolver->setAllowedTypes('faitMarquant', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a commenté le fait marquant %s sur le projet %s.',
            '<i class="fa fa-comment" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['user']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postPersist(FaitMarquantComment $faitMarquantComment, LifecycleEventArgs $args): void
    {
        $faitMarquant = $faitMarquantComment->getFaitMarquant();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($faitMarquant->getProjet()->getId()),
                'user' => intval($faitMarquantComment->getCreatedBy()->getUser()->getId()),
                'faitMarquant' => intval($faitMarquant->getId()),
            ])
        ;

        $em = $args->getEntityManager();

        $targets = [];
        if ($faitMarquant->getProjet()->getChefDeProjet()->getUser() !== $faitMarquantComment->getCreatedBy()->getUser()){
            $targets[] = $faitMarquant->getProjet()->getChefDeProjet();
        }
        if ($faitMarquant->getCreatedBy()->getUser() !== $faitMarquantComment->getCreatedBy()->getUser()){
            if (!in_array($faitMarquant->getCreatedBy(), $targets))  $targets[] = $faitMarquant->getCreatedBy();
        }

        foreach ($targets as $target) {
            $em->persist(SocieteUserNotification::create($activity, $target));
        }

        $em->persist($activity);
        $em->flush();
    }
}
