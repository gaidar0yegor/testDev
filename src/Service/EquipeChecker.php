<?php

namespace App\Service;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Security\Role\RoleSociete;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EquipeChecker
{
    private SocieteChecker $societeChecker;

    private AuthorizationCheckerInterface $authChecker;

    private UserContext $userContext;

    public function __construct(
        SocieteChecker $societeChecker,
        AuthorizationCheckerInterface $authChecker,
        UserContext $userContext
    ) {
        $this->societeChecker = $societeChecker;
        $this->authChecker = $authChecker;
        $this->userContext = $userContext;
    }

    /**
     * @return bool Si $equipeMember est dans l'équipe de $societeUserSuperior.
     */
    public function isSameEquipe(SocieteUser $equipeMember, SocieteUser $societeUserSuperior): bool
    {
        return
            $this->societeChecker->isSameSociete($equipeMember,$societeUserSuperior) &&
            (
                $equipeMember === $societeUserSuperior ||
                $equipeMember->getMySuperior() === $societeUserSuperior ||
                (
                    null !== $equipeMember->getMySuperior() &&
                    $equipeMember->getMySuperior()->getMySuperior() === $societeUserSuperior
                )
            );
    }

    /*
     * Vérifier si current SocieteUser a la permission pour modifier, supprimer ... un utilisateur
     * current SocieteUser peut être (soit un ADMIN soit un N+1)
     * $societeUser : utilisateur à editer, supprimer ...
     */
    public function hasPermission(SocieteUser $societeUser = null):bool
    {
        if ($societeUser instanceof SocieteUser && !$this->societeChecker->isSameSociete($societeUser,$this->userContext->getSocieteUser())){
            return false;
        }

        if ($this->authChecker->isGranted(RoleSociete::ADMIN)){
            return true;
        }

        if (!ProductPrivilegeCheker::checkProductPrivilege($this->userContext->getSocieteUser()->getSociete(), ProductPrivileges::SOCIETE_HIERARCHICAL_SUPERIOR)){
            return false;
        }

        if ($this->userContext->getSocieteUser()->isSuperiorFo()){
            if ($societeUser instanceof SocieteUser){
                return $this->isSameEquipe($societeUser,$this->userContext->getSocieteUser());
            }
            return true;
        }

        return false;
    }
}
