<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\Evenement;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserEvenementNotification;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementModifiedActivity implements ActivityInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    public function __construct(EntityLinkService $entityLinkService, UserContext $userContext, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
    }

    public static function getType(): string
    {
        return 'evenement_modified';
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
            'modifiedBy'
        ]);

        $resolver->setAllowedTypes('evenement', 'integer');
        $resolver->setAllowedTypes('projet', ['integer','string']);
        $resolver->setAllowedTypes('modifiedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        $textProjet = $activityParameters['projet']
            ? ' sur le projet ' . $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            : '';

        return sprintf(
            '%s %s a modifié l\'évènement %s%s.',
            '<i class="fa fa-calendar" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
            $this->entityLinkService->generateLink(Evenement::class, $activityParameters['evenement']),
            $textProjet
        );
    }

    public function postUpdate(Evenement $evenement, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'evenement' => intval($evenement->getId()),
                'projet' => $evenement->getProjet() ? intval($evenement->getProjet()->getId()) : '',
                'modifiedBy' => intval($this->userContext->getSocieteUser()->getId()),
            ])
        ;

        if ($evenement->getProjet()){
            $projetActivity = new ProjetActivity();
            $projetActivity
                ->setProjet($evenement->getProjet())
                ->setActivity($activity)
            ;
            $em->persist($projetActivity);
        }

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant) {
            if ($evenementParticipant->getSocieteUser() !== $this->userContext->getSocieteUser()){
                $em->persist(SocieteUserEvenementNotification::create($activity, $evenementParticipant->getSocieteUser()));
            }
        }

        $em->persist($activity);

        $em->flush();
    }
}
