<?php

namespace App\Twig;

use App\Entity\Projet;
use App\Entity\User;
use App\Exception\RdiException;
use App\Service\ParticipantService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ParticipantExtension extends AbstractExtension
{
    private ParticipantService $participantService;

    private TokenStorageInterface $tokenStorage;

    public function __construct(ParticipantService $participantService, TokenStorageInterface $tokenStorage)
    {
        $this->participantService = $participantService;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFilters(): array
    {
        return [
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('userRoleOn', [$this, 'userRoleOn']),
        ];
    }

    public function userRoleOn(Projet $projet, User $user = null): ?string
    {
        if (null === $user) {
            $token = $this->tokenStorage->getToken();

            if (null !== $token) {
                $user = $this->tokenStorage->getToken()->getUser();
            }
        }

        if (null === $user) {
            throw new RdiException('Twig function "userRoleOn" must be provided an $user when no user logged in');
        }

        return $this->participantService->getRoleOfUserOnProjet($user, $projet);
    }
}
