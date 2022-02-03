<?php

namespace App\Twig;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Service\EquipeChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EquipeExtension extends AbstractExtension
{
    private UserContext $userContext;
    private EquipeChecker $equipeChecker;

    public function __construct(UserContext $userContext, EquipeChecker $equipeChecker)
    {
        $this->userContext = $userContext;
        $this->equipeChecker = $equipeChecker;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isMemberOfMyTeam', [$this, 'isMemberOfMyTeam']),
            new TwigFunction('hasUserManagementPermission', [$this, 'hasUserManagementPermission']),
        ];
    }

    public function isMemberOfMyTeam(SocieteUser $teamMember) :bool
    {
        return $this->equipeChecker->isSameEquipe($teamMember, $this->userContext->getSocieteUser());
    }

    public function hasUserManagementPermission(SocieteUser $teamMember = null) :bool
    {
        return $this->equipeChecker->hasPermission($teamMember);
    }
}
