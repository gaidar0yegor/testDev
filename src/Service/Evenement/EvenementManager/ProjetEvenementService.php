<?php

namespace App\Service\Evenement\EvenementManager;

use App\Entity\Evenement;
use App\Entity\EvenementParticipant;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Service\Evenement\EvenementService;
use App\Service\ParticipantService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjetEvenementService extends EvenementService
{
    public function serializeProjetEvenements(Projet $projet): array
    {
        $response = [];

        foreach ($projet->getEvenements() as $evenement){
            $response['data'][] = $this->serializeEvenement($evenement, Evenement::PROJET_EVENEMENT_TYPES);
        }

        $societeUsers = $projet->getProjetParticipants()->map(function (ProjetParticipant $projetParticipant){
            return $projetParticipant->getSocieteUser();
        })->toArray();

        $response['collections'] = $this->generateDhtmlxCollections($societeUsers, Evenement::PROJET_EVENEMENT_TYPES);

        return $response;
    }
}
