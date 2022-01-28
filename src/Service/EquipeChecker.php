<?php

namespace App\Service;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Security\Role\RoleSociete;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EquipeChecker
{
    private AuthorizationCheckerInterface $authChecker;

    private UserContext $userContext;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        UserContext $userContext
    ) {
        $this->authChecker = $authChecker;
        $this->userContext = $userContext;
    }

    /**
     * @return bool Si $equipeMember est dans l'équipe de $societeUserSuperior.
     */
    public function isSameEquipe(SocieteUser $equipeMember, SocieteUser $societeUserSuperior): bool
    {
        return $equipeMember->getMySuperior() === $societeUserSuperior ||
            (null !== $equipeMember->getMySuperior() && $equipeMember->getMySuperior()->getMySuperior() === $societeUserSuperior);
    }

    /*
     * Vérifier si current SocieteUser a la permission pour modifier, supprimer ... un utilisateur
     * current SocieteUser peut être (soit un ADMIN soit un N+1)
     * $societeUser : utilisateur à editer, supprimer ...
     */
    public function hasPermission(SocieteUser $societeUser = null):bool
    {
        if ($this->authChecker->isGranted(RoleSociete::ADMIN)){
            return true;
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
