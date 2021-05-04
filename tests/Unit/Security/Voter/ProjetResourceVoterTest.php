<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Security\Role\RoleProjet;
use App\Security\Role\RoleSociete;
use App\Security\Voter\ProjetResourceVoter;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjetResourceVoterTest extends TestCase
{
    /**
     * @var UserContext
     */
    private $userContextMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContext::class);
    }

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
            $this->userContextMock,
            new SocieteChecker()
        );

        $societe = new Societe();

        $user = new SocieteUser();
        $user
            ->setSociete($societe)
            ->setRole(RoleSociete::ADMIN)
        ;

        $cdp = new SocieteUser();
        $cdp
            ->setSociete($societe)
            ->setRole(RoleSociete::CDP)
        ;

        $projet = new Projet();
        $projet
            ->setSociete($societe)
        ;

        ProjetParticipant::create($cdp, $projet, RoleProjet::CDP);

        $adminCanCreate = $voter->userCanCreateResourceOnProjet($user, $projet);

        $this->assertTrue(
            $adminCanCreate,
            'L\'administrateur peut créer des ressources sur les projets dont il ne participe pas'
        );
    }
}
