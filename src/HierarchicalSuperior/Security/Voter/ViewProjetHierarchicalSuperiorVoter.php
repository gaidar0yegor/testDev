<?php

namespace App\HierarchicalSuperior\Security\Voter;

use App\Entity\Projet;
use App\MultiSociete\UserContext;
use App\Repository\ProjetRepository;
use App\Repository\SocieteUserRepository;
use App\Service\SocieteChecker;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Security voter pour vérifier qu'un user
 * peut effectuer une action sur un projet donné.
 */
class ViewProjetHierarchicalSuperiorVoter extends Voter
{
    public const VIEW = 'VIEW_PROJET_HIERARCHICAL_SUPERIOR';

    private UserContext $userContext;
    private SocieteUserRepository $societeUserRepository;
    private ProjetRepository $projetRepository;
    private SocieteChecker $societeChecker;

    public function __construct(
        UserContext $userContext,
        SocieteUserRepository $societeUserRepository,
        ProjetRepository $projetRepository,
        SocieteChecker $societeChecker
    ) {
        $this->userContext = $userContext;
        $this->societeUserRepository = $societeUserRepository;
        $this->projetRepository = $projetRepository;
        $this->societeChecker = $societeChecker;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return self::VIEW === $attribute;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (!$subject instanceof Projet) {
            throw new RuntimeException('Expected an instance of Projet');
        }

        if (!$this->societeChecker->isSameSociete($subject, $this->userContext->getSocieteUser())){
            throw new AccessDeniedException();
        }

        if (!ProductPrivilegeCheker::checkProductPrivilege($this->userContext->getSocieteUser()->getSociete(), ProductPrivileges::SOCIETE_HIERARCHICAL_SUPERIOR)){
            throw new RuntimeException("Indisponible avec votre license.");
        }

        if (!$this->userContext->getSocieteUser()->isSuperiorFo()){
            throw new RuntimeException("Vous n'êtes pas un chef de projet.");
        }

        $projets = $this->projetRepository->findAllForUsers(
            $this->societeUserRepository->findTeamMembers($this->userContext->getSocieteUser())
        );

        if (!in_array($subject,$projets)) {
            throw new AccessDeniedException('Aucun membre de votre équipe fait partie de ce projet.');
        }

        return true;
    }
}
