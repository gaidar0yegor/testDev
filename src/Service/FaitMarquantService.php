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

        $faitMarquant->setTitre('Projet suspendu');
        $faitMarquant->setDescription('Suspension temporaire du projet pour des raisons stratégiques internes. Plus aucune action (saisie de faits marquants, suivi du temps, …) ne sera possible sur ce projet jusqu’à sa réactivation. Seule la consultation de la page projet sera possible durant toute la période de suspension. Pour toute information complémentaire, veuillez contacter le responsable des projets.');
        $faitMarquant->setDate(new \DateTime());

        $faitMarquant->setProjet($projet);
        $faitMarquant->setCreatedBy($this->userContext->getSocieteUser());

        return $faitMarquant;
    }

    /**
     * Créer un fait marquant de réactivation de projet
     */
    public function CreateFmOfProjectResume(Projet $projet): FaitMarquant
    {
        $faitMarquant = new FaitMarquant();

        $faitMarquant->setTitre('Projet réactivé');
        $faitMarquant->setDescription('Réactivation du projet après validation en interne avec les principales parties prenantes. Les contributeurs du projet peuvent à nouveau réaliser des actions (saisie de faits marquants, suivi du temps, …) sur ce projet. Pour toute information complémentaire, veuillez contacter le responsable des projets.');
        $faitMarquant->setDate(new \DateTime());

        $faitMarquant->setProjet($projet);
        $faitMarquant->setCreatedBy($this->userContext->getSocieteUser());

        return $faitMarquant;
    }
}
