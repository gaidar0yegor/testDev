<?php

namespace App\Twig;

use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Security\Role\RoleProjet;
use App\Service\ParticipantService;
use App\MultiSociete\UserContext;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ParticipantExtension extends AbstractExtension
{
    private ParticipantService $participantService;

    private UserContext $userContext;

    public function __construct(ParticipantService $participantService, UserContext $userContext)
    {
        $this->participantService = $participantService;
        $this->userContext = $userContext;
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
            new TwigFunction('roleSortValue', [$this, 'roleSortValue']),
            new TwigFunction('isAccessibleFichierProjet', [$this, 'isAccessibleFichierProjet']),
        ];
    }

    public function userRoleOn(Projet $projet, SocieteUser $societeUser = null): ?string
    {
        if (null === $societeUser) {
            $societeUser = $this->userContext->getSocieteUser();
        }

        if (null === $societeUser) {
            throw new RuntimeException('Twig function "userRoleOn" must be provided a $societeUser when no user logged in');
        }

        return $this->participantService->getRoleOfUserOnProjet($societeUser, $projet);
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
        return $this->participantService->sortByRole($projetParticipants, $ascOrDesc);
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

    /**
     * Get role as number. Used to sort.
     */
    public function roleSortValue(string $role): int
    {
        return array_search($role, RoleProjet::getRoles());
    }

    public function isAccessibleFichierProjet(FichierProjet $fichierProjet) :bool
    {
        return $fichierProjet->getSocieteUsers()->contains($this->userContext->getSocieteUser());
    }
}
