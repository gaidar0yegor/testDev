<?php

namespace App\Service;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;

class FaitMarquantService
{
    private EntityManagerInterface $em;

    private UserContext $userContext;

    public function __construct(EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->userContext = $userContext;
    }

    /**
     * Créer un fait marquant de suspension de projet
     */
    public function CreateFmOfProjectSuspension(Projet $projet): FaitMarquant
    {
        $faitMarquant = new FaitMarquant();

        $faitMarquant->setTitre('Fait marquant de suspension du projet');
        $faitMarquant->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
        $faitMarquant->setDate(new \DateTime());

        $faitMarquant->setProjet($projet);
        $faitMarquant->setCreatedBy($this->userContext->getSocieteUser());

        return $faitMarquant;
    }
}
