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

class SocieteUserEvenementService extends EvenementService
{
    /**
     * @param array $societeUser
     * @param array $evenementTypes
     *
     * @return array
     */
    public function serializeSocieteUsersEvenements(array $societeUsers, array $evenementTypes = Evenement::EVENEMENT_TYPES): array
    {
        $response = [];
        $evenements = new ArrayCollection();


        foreach ($societeUsers as $societeUser){
            foreach ($societeUser->getEvenementParticipants() as $evenementParticipant){
                if (
                    $evenements->contains($evenementParticipant->getEvenement()) ||
                    !in_array($evenementParticipant->getEvenement()->getType(), $evenementTypes)
                ){
                    continue;
                }

                $evenements->add($evenementParticipant->getEvenement());
                $response['data'][] = $this->serializeEvenement($evenementParticipant->getEvenement(), Evenement::SOCIETE_USER_EVENEMENT_TYPES);
            }
        }

        $response['collections'] = $this->generateDhtmlxCollections($societeUsers, Evenement::SOCIETE_USER_EVENEMENT_TYPES);

        return $response;
    }
}
