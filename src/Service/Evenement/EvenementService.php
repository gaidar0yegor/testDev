<?php

namespace App\Service\Evenement;

use App\Entity\Evenement;
use App\Entity\EvenementParticipant;
use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Service\ParticipantService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class EvenementService
{
    protected EntityManagerInterface $em;
    protected UserContext $userContext;
    protected TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        UserContext $userContext,
        TranslatorInterface $translator
    )
    {
        $this->em = $em;
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    protected static function getEvenementParticipantBySocieteUser(Evenement $evenement, SocieteUser $societeUser, bool $required = null): ?EvenementParticipant
    {
        foreach ($evenement->getEvenementParticipants() as $evenementParticipant) {
            if ($evenementParticipant->getSocieteUser() === $societeUser) {
                if ( $required === null || $evenementParticipant->getRequired() === $required ){
                    return $evenementParticipant;
                }
            }
        }

        return null;
    }

    public function serializeEvenement(Evenement $evenement, array $editableTypes = Evenement::EVENEMENT_TYPES): array
    {
        $readonly = !($evenement->getCreatedBy() === $this->userContext->getSocieteUser() && in_array($evenement->getType(), $editableTypes)) ;

        $serializedEvenement =  [
            'id' => $evenement->getId(),
            'text' => $evenement->getText(),
            'description' => $evenement->getDescription(),
            'location' => $evenement->getLocation(),
            'start_date' => $evenement->getStartDate()->format('Y-m-d H:i'),
            'end_date' => $evenement->getEndDate()->format('Y-m-d H:i'),
            'eventType' => $evenement->getType(),

            'projetId' => $evenement->getProjet() ? $evenement->getProjet()->getId() : null,
            'projetAcronyme' => $evenement->getProjet() ? $evenement->getProjet()->getAcronyme() : null,

            'readonly' => $readonly,
            'createdByFullname' => $evenement->getCreatedBy()->getUser()->getFullnameOrEmail(),

            'is_invited' => $this->userContext->getSocieteUser()->isInvitedToEvenement($evenement)
        ];

        $requiredParticipants = $evenement->getEvenementParticipants()->filter(function(EvenementParticipant $evenementParticipant) {
            return $evenementParticipant->getRequired() === true;
        });
        $optionalParticipants = $evenement->getEvenementParticipants()->filter(function(EvenementParticipant $evenementParticipant) {
            return $evenementParticipant->getRequired() === false;
        });

        $serializedEvenement['required_participants_ids'] = implode(",", $requiredParticipants->map(function($evenementParticipant){ return $evenementParticipant->getSocieteUser()->getId(); })->getValues());
        $serializedEvenement['required_participants_names'] = implode(" | ", $requiredParticipants->map(function($evenementParticipant){ return $evenementParticipant->getSocieteUser()->getUser()->getFullnameOrEmail(); })->getValues());

        $serializedEvenement['optional_participants_ids'] = implode(",", $optionalParticipants->map(function($evenementParticipant){ return $evenementParticipant->getSocieteUser()->getId(); })->getValues());
        $serializedEvenement['optional_participants_names'] = implode(" | ", $optionalParticipants->map(function($evenementParticipant){ return $evenementParticipant->getSocieteUser()->getUser()->getFullnameOrEmail(); })->getValues());

        return $serializedEvenement;
    }

    public function saveEvenementFromRequest(Request $request, Projet $projet = null, Evenement $evenement = null): Evenement
    {
        if (null === $evenement) {
            $evenement = new Evenement();
        }

        $evenement->setProjet($projet);
        $evenement->setCreatedBy($this->userContext->getSocieteUser());

        $evenement->setText($request->request->get('text'));
        $evenement->setDescription($request->request->get('description'));
        $evenement->setLocation($request->request->get('location'));
        $evenement->setStartDate(\DateTime::createFromFormat('Y-m-d H:i', $request->request->get('start_date')));
        $evenement->setEndDate(\DateTime::createFromFormat('Y-m-d H:i', $request->request->get('end_date')));
        if ($request->request->has('eventType')) $evenement->setType($request->request->get('eventType'));

        $evenement = self::createEvenementParticipants(
            $evenement,
            array_map('intval', explode(',', $request->request->get('required_participants_ids'))),
            array_map('intval', explode(',', $request->request->get('optional_participants_ids')))
        );

        return $evenement;
    }

    public function createEvenementParticipants(
        Evenement $evenement,
        array $required_ids,
        array $optional_ids
    ) : Evenement
    {
        $optional_ids = array_diff($optional_ids, $required_ids);

        $requiredSocieteUsers = $this->em->getRepository(SocieteUser::class)->findBy( array('id' => $required_ids) );
        $optionalSocieteUsers = $this->em->getRepository(SocieteUser::class)->findBy( array('id' => $optional_ids) );

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant){
            $evenement->removeEvenementParticipant($evenementParticipant);
        }

        foreach ($requiredSocieteUsers as $societeUser){
            $evenement->addEvenementParticipant(EvenementParticipant::create($evenement, $societeUser, true));
        }

        foreach ($optionalSocieteUsers as $societeUser){
            $evenement->addEvenementParticipant(EvenementParticipant::create($evenement, $societeUser, false));
        }

        return $evenement;
    }

    /**
     * @param SocieteUser[] $societeUsers
     * @param array $types
     *
     * @return array
     */
    public function generateDhtmlxCollections(array $societeUsers, array $types): array
    {
        $collections = [];

        foreach ($societeUsers as $societeUser){
            $collections['participants'][] = [
                'value' => $societeUser->getId(),
                'label' => " " . $societeUser->getUser()->getFullnameOrEmail()
            ];
        }

        foreach ($types as $type){
            $collections['eventTypes'][] = ['value' => $type, 'label' => $this->translator->trans($type)];
        }

        return $collections;
    }
}
