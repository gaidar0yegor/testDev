<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Repository\ProjetParticipantRepository;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

class ProjetLastActionService implements EventSubscriberInterface
{
    private ProjetParticipantRepository $projetParticipantRepository;

    private ProjetRepository $projetRepository;

    private EntityManagerInterface $em;

    private UserContext $userContext;

    public function __construct(
        ProjetParticipantRepository $projetParticipantRepository,
        ProjetRepository $projetRepository,
        EntityManagerInterface $em,
        UserContext $userContext
    ) {
        $this->projetParticipantRepository = $projetParticipantRepository;
        $this->projetRepository = $projetRepository;
        $this->em = $em;
        $this->userContext = $userContext;
    }

    public function updateLastViewedAction(SocieteUser $societeUser, Projet $projet): void
    {
        $projetParticipant = $this->projetParticipantRepository->findOneBy([
            'societeUser' => $societeUser,
            'projet' => $projet,
        ]);

        if (null === $projetParticipant) {
            return;
        }

        $projetParticipant->setLastActionAtNow();
    }

    /**
     * @return Projet[]
     */
    public function findRecentProjetsForUser(?SocieteUser $societeUser = null): array
    {
        if (null === $societeUser) {
            $societeUser = $this->userContext->getSocieteUser();
        }

        return $this->projetRepository->findRecentsForUser($societeUser);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller_arguments' => 'onKernelControllerArguments',
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        if ('corp_app_fo_projet' !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $projet = null;

        foreach ($event->getArguments() as $argument) {
            if ($argument instanceof Projet) {
                $projet = $argument;
                break;
            }
        }

        if (null === $projet) {
            throw new RuntimeException('corp_app_fo_projet route expected to have a Projet in its controller arguments');
        }

        $this->updateLastViewedAction($this->userContext->getSocieteUser(), $projet);
        $this->em->flush();
    }
}
