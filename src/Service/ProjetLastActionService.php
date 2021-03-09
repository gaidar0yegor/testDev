<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\User;
use App\Repository\ProjetParticipantRepository;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Security\Core\Security;

class ProjetLastActionService implements EventSubscriberInterface
{
    private ProjetParticipantRepository $projetParticipantRepository;

    private ProjetRepository $projetRepository;

    private EntityManagerInterface $em;

    private Security $security;

    public function __construct(
        ProjetParticipantRepository $projetParticipantRepository,
        ProjetRepository $projetRepository,
        EntityManagerInterface $em,
        Security $security
    ) {
        $this->projetParticipantRepository = $projetParticipantRepository;
        $this->projetRepository = $projetRepository;
        $this->em = $em;
        $this->security = $security;
    }

    public function updateLastViewedAction(User $user, Projet $projet): void
    {
        $projetParticipant = $this->projetParticipantRepository->findOneBy([
            'user' => $user,
            'projet' => $projet,
        ]);

        if (null === $projetParticipant) {
            return;
        }

        $projetParticipant->setLastActionAtNow();

        $this->em->flush();
    }

    /**
     * @return Projet[]
     */
    public function findRecentProjetsForUser(?User $user = null): array
    {
        if (null === $user) {
            $user = $this->security->getUser();
        }

        return $this->projetRepository->findRecentsForUser($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller_arguments' => 'onKernelControllerArguments',
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        if ('app_fo_projet' !== $event->getRequest()->attributes->get('_route')) {
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
            throw new RuntimeException('app_fo_projet route expected to have a Projet in its controller arguments');
        }

        $this->updateLastViewedAction($this->security->getUser(), $projet);
    }
}
