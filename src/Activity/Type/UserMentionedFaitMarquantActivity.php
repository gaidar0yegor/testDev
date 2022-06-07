<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserMentionedFaitMarquantActivity implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    public function __construct(EntityLinkService $entityLinkService, UserContext $userContext)
    {
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
    }

    public static function getType(): string
    {
        return 'fait_marquant_user_mentioned';
    }

    public static function getFilterType(): string
    {
        return 'fait_marquant';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'mentionedBy',
            'faitMarquant',
            'projet',
        ]);

        $resolver->setAllowedTypes('mentionedBy', 'integer');
        $resolver->setAllowedTypes('faitMarquant', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s vous a mentionné dans le fait marquant %s sur le projet %s.',
            '<i class="fa fa-comment" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['mentionedBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postPersist(FaitMarquant $faitMarquant, LifecycleEventArgs $args): void
    {
        $this->mentionSocieteUser($args->getEntityManager(), $faitMarquant);
    }
    public function postUpdate(FaitMarquant $faitMarquant, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($faitMarquant);

        if (!isset($changes['description'])){
            $args->getEntityManager()->flush();
            return;
        }

        $this->mentionSocieteUser($args->getEntityManager(), $faitMarquant, $changes['description'][0]);
    }

    private function mentionSocieteUser(EntityManagerInterface $em, FaitMarquant $faitMarquant, string $oldDescription = "")
    {
        $mentionsSocieteUser = $this->getMentionedSocieteUser($faitMarquant->getDescription());
        $oldMentionsSocieteUser = $this->getMentionedSocieteUser($oldDescription);

        if (count($mentionsSocieteUser) > 0){
            $activity = new Activity();
            $activity
                ->setType(self::getType())
                ->setParameters([
                    'mentionedBy' => intval($this->userContext->getSocieteUser()->getId()),
                    'faitMarquant' => intval($faitMarquant->getId()),
                    'projet' => intval($faitMarquant->getProjet()->getId()),
                ])
            ;

            foreach ($mentionsSocieteUser as $mentionId){
                if (!in_array($mentionId,$oldMentionsSocieteUser) && $mentionId != $this->userContext->getSocieteUser()->getId()){
                    $mentionedTo = $em->getRepository(SocieteUser::class)->find($mentionId);
                    $societeUserNotification = SocieteUserNotification::create($activity, $mentionedTo);
                    $em->persist($societeUserNotification);
                    $em->persist($activity);
                }
            }
        }

        $em->flush();
    }

    private function getMentionedSocieteUser(string $fmDescription) :array
    {
        $crawler = new Crawler($fmDescription);
        $mentions = $crawler->filter('a.mention-user_societe');
        $mentionsSocieteUser = [];
        foreach ($mentions as $mention){
            $mentionsSocieteUser[] = (int)$mention->getAttribute('data-id');
        }

        return $mentionsSocieteUser;
    }
}
