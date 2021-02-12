<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\User;
use App\Role;
use App\Security\Voter\ProjetResourceVoter;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjetResourceVoterTest extends TestCase
{
    public function testUserCanCreateResourceOnProjetAllowsAdminToCreateResourceOnProjet()
    {
        $voter = new ProjetResourceVoter(
            new class () implements AuthorizationCheckerInterface
            {
                public function isGranted($attributes, $subject = null)
                {
                    return true;
                }
            },
            new ParticipantService(),
            new SocieteChecker()
        );

        $societe = new Societe();

        $user = new User();
        $user
            ->setSociete($societe)
            ->setRole('ROLE_FO_ADMIN')
        ;

        $cdp = new User();
        $cdp
            ->setSociete($societe)
            ->setRole('ROLE_FO_CDP')
        ;

        $projet = new Projet();
        $projet
            ->setSociete($societe)
            ->addProjetParticipant(ProjetParticipant::create($cdp, $projet, Role::CDP))
        ;

        $adminCanCreate = $voter->userCanCreateResourceOnProjet($user, $projet);

        $this->assertTrue(
            $adminCanCreate,
            'L\'administrateur peut créer des ressources sur les projets dont il ne participe pas'
        );
    }
}
