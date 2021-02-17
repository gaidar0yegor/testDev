<?php

namespace App\RegisterSociete;

use App\Entity\Projet;
use App\Entity\User;
use App\RegisterSociete\DTO\InviteCollaborators;
use App\Role;
use App\Service\Invitator;

class InviteCollaboratorsService
{
    private Invitator $invitator;

    public function __construct(Invitator $invitator)
    {
        $this->invitator = $invitator;
    }

    public function inviteCollaborators(
        InviteCollaborators $inviteCollaborators,
        User $admin,
        Projet $projet = null
    ): void {
        $users = [];
        $societe = $admin->getSociete();

        if (null !== $inviteCollaborators->getEmail0()) {
            $users[] = $this->invitator
                ->initUser($societe)
                ->setEmail($inviteCollaborators->getEmail0())
                ->setRole($inviteCollaborators->getRole0())
            ;
        }

        if (null !== $inviteCollaborators->getEmail1()) {
            $users[] = $this->invitator
                ->initUser($societe)
                ->setEmail($inviteCollaborators->getEmail1())
                ->setRole($inviteCollaborators->getRole1())
            ;
        }

        if (null !== $projet) {
            foreach ($users as $user) {
                $this->invitator->addParticipation($user, $projet, Role::CONTRIBUTEUR);
            }
        }

        foreach ($users as $user) {
            $this->invitator->check($user);
            $this->invitator->sendInvitation($user, $admin);
        }
    }
}
