<?php

namespace App\Service\Evenement;

use App\DTO\EvenementUpdatesCra;
use App\Entity\Evenement;
use App\Entity\EvenementParticipant;
use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Service\CraService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class EvenementService
{
    protected EntityManagerInterface $em;
    protected UserContext $userContext;
    protected TranslatorInterface $translator;
    protected CraService $craService;

    public function __construct(
        EntityManagerInterface $em,
        UserContext $userContext,
        TranslatorInterface $translator,
        CraService $craService
    )
    {
        $this->em = $em;
        $this->userContext = $userContext;
        $this->translator = $translator;
        $this->craService = $craService;
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
            'minutesToReminde' => (string)$evenement->getMinutesToReminde(),

            'projetId' => $evenement->getProjet() ? $evenement->getProjet()->getId() : null,
            'projetAcronyme' => $evenement->getProjet() ? $evenement->getProjet()->getAcronyme() : null,

            'readonly' => $readonly,
            'createdByFullname' => $evenement->getCreatedBy()->getUser()->getFullnameOrEmail(),

            'is_invited' => $this->userContext->getSocieteUser()->isInvitedToEvenement($evenement),

            'external_participants_emails' => implode(";", $evenement->getExternalParticipantEmails()),

            'auto_update_cra' => $evenement->getAutoUpdateCra(),
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
        $evenementUpdatesCra = (new EvenementUpdatesCra())
            ->setOldEvenement($evenement);

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

        if ($request->request->has('minutesToReminde')){
            $minutesToReminde = (int)$request->request->get('minutesToReminde');
            $evenement->setMinutesToReminde($minutesToReminde);

            $reminderAt = (clone $evenement->getStartDate())->modify("-" . $minutesToReminde . " minutes");
            $evenement->setReminderAt($reminderAt);
            $evenement->setIsReminded($reminderAt <= (new \DateTime()));
        }

        $evenement->setAutoUpdateCra($request->request->get('auto_update_cra') == true);

        if ($request->request->has('external_participants_emails') && $request->request->get('external_participants_emails'))
            $evenement->setExternalParticipantEmails(explode(';', $request->request->get('external_participants_emails')));

        $evenement = $this->createEvenementParticipants(
            $evenement,
            array_map('intval', explode(',', $request->request->get('required_participants_ids'))),
            array_map('intval', explode(',', $request->request->get('optional_participants_ids')))
        );

        if ($evenement->getAutoUpdateCra()){
            $evenementUpdatesCra->setNewEvenement($evenement);
            $this->updateSocieteUsersCra($evenementUpdatesCra);
        }

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

    public function checkOverlapEventsParticipants(\DateTime $startDate, \DateTime $endDate, array $societeUserIds, int $exceptEventId = null)
    {
        return $this->em->getRepository(EvenementParticipant::class)->checkOverlapEvenements($startDate, $endDate, $societeUserIds, $exceptEventId);
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
                'label' => $societeUser->getUser()->getFullnameOrEmail()
            ];
        }

        foreach ($types as $type){
            $collections['eventTypes'][] = ['value' => $type, 'label' => $this->translator->trans($type)];
        }

        $collections['reminderChoices'] = EvenementReminderChoices::getChoices($this->translator);

        return $collections;
    }

    public function updateSocieteUsersCra(EvenementUpdatesCra $evenementUpdatesCra): void
    {
        if ($evenementUpdatesCra->getOldEvenement()){
            $yearsMonths = $evenementUpdatesCra->getOldMonthsCraDays();
            foreach ($evenementUpdatesCra->getOldSocieteUsers() as $societeUser){
                $this->updateCraJours($societeUser, $yearsMonths, 1);
            }
        }
        if ($evenementUpdatesCra->getNewEvenement()){
            $yearsMonths = $evenementUpdatesCra->getNewMonthsCraDays();
            foreach ($evenementUpdatesCra->getNewSocieteUsers() as $societeUser){
                $this->updateCraJours($societeUser, $yearsMonths, 0);
            }
        }
    }

    private function updateCraJours(SocieteUser $societeUser, array $yearsMonths, int $value)
    {
        foreach ($yearsMonths as $year => $monthsDays){
            foreach ($monthsDays as $month => $days){
                $cra = $this->craService->loadCraForUser($societeUser, new \DateTime('01-' . $month . '-' . $year));
                $craJours = $cra->getJours();
                foreach ($days as $day){
                    $craJours[$day - 1] = $value;
                }
                $cra
                    ->setJours($craJours)
                    ->setCraModifiedAt(new \DateTime())
                ;
                if ($value !== 0){
                    $this->craService->uncheckWeekEnds($cra);
                    $this->craService->uncheckJoursFeries($cra);
                }
                $this->em->persist($cra);
            }
        }
    }
}
