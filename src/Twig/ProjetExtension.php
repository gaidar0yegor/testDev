<?php

namespace App\Twig;

use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Repository\ProjetActivityRepository;
use App\Security\Role\RoleProjet;
use App\Service\ParticipantService;
use App\MultiSociete\UserContext;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ProjetExtension extends AbstractExtension
{
    private ProjetActivityRepository $projetActivityRepository;

    public function __construct(ProjetActivityRepository $projetActivityRepository)
    {
        $this->projetActivityRepository = $projetActivityRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getProjetActivities', [$this, 'getProjetActivities']),
        ];
    }

    public function getProjetActivities(Projet $projet, ?\DateTime $infDate = null, ?\DateTime $supDate = null) :array
    {
        return $this->projetActivityRepository->findByProjet($projet,null,$infDate,$supDate);
    }
}
