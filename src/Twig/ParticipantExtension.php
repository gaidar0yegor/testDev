<?php

namespace App\Twig;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\User;
use App\Exception\RdiException;
use App\Role;
use App\Service\ParticipantService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
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
            new TwigFilter('sortByRole', [$this, 'sortByRole']),
            new TwigFilter('filterByRoleExactly', [$this, 'filterByRoleExactly']),
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

    /**
     * Sort projetParticipants list by role (Chef de projet, then Contributeur, then Observateur)
     *
     * @param iterable $projetParticipants
     * @param string $order "asc" or "desc"
     *
     * @return ProjetParticipant[]
     */
    public function sortByRole(iterable $projetParticipants, string $ascOrDesc = 'desc'): array
    {
        $roleOrder = [
            Role::CDP => 3,
            Role::CONTRIBUTEUR => 2,
            Role::OBSERVATEUR => 1,
        ];

        $order = 'desc' === strtolower($ascOrDesc) ? 1 : -1;
        $sorted = iterator_to_array($projetParticipants);

        usort(
            $sorted,
            function (ProjetParticipant $a, ProjetParticipant $b) use ($order, $roleOrder) {
                return ($roleOrder[$b->getRole()] - $roleOrder[$a->getRole()]) * $order;
            }
        );

        return $sorted;
    }

    /**
     * Filter by exact role (does not return CDP if we filter CONTRIBUTEUR).
     *
     * @param iterable $projetParticipants
     * @param string $role
     *
     * @return ProjetParticipant[]
     */
    public function filterByRoleExactly(iterable $projetParticipants, string $role): array
    {
        return $this->participantService->getProjetParticipantsWithRoleExactly($projetParticipants, $role);
    }
}
