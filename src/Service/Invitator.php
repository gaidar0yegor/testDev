<?php

namespace App\Service;

use App\DTO\InitSociete;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\SocieteUserInvitationNotification;
use App\Security\Role\RoleSociete;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Service qui s'occupe d'inviter des nouveaux utilisateurs
 * sur RDI-Manager, en leur envoyant un lien d'invitation.
 * Utilisation :
 *
 * // Init user instance
 * $user = $invitator->initUser($this->getUser()->getSociete());
 *
 * // Set mail (or set by form)
 * $user->setEmail($invitation->getEmail());
 *
 * // Optionally add user on projet with a role
 * $invitator->addParticipation($user, $projet, RoleProjet::CONTRIBUTEUR);
 *
 * // Check $user instance before finish
 * $invitator->check($user);
 *
 * // Send invitation link
 * $invitator->sendInvitation($user, $this->getUser());
 *
 * // Flush all entities persisted by invitator
 * $em->flush();
 */
class Invitator
{
    private TokenGenerator $tokenGenerator;

    private ValidatorInterface $validator;

    private EntityManagerInterface $em;

    private SocieteInitializer $societeInitializer;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        TokenGenerator $tokenGenerator,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        SocieteInitializer $societeInitializer,
        EventDispatcherInterface $dispatcher
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->validator = $validator;
        $this->em = $em;
        $this->societeInitializer = $societeInitializer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Initialise l'utilisateur qui va être invité.
     */
    public function initUser(Societe $societe, string $role = RoleSociete::USER): SocieteUser
    {
        $societeUser = new SocieteUser();

        $this->em->persist($societeUser);

        $societeUser
            ->setSociete($societe)
            ->setRole($role)
            ->setInvitationToken($this->tokenGenerator->generateUrlToken())
            ->addSocieteUserPeriod(new SocieteUserPeriod());

        return $societeUser;
    }

    public function initSociete(InitSociete $initSociete): Societe
    {
        return $this->societeInitializer->initializeSociete($initSociete);
    }

    /**
     * Ajoute l'utilisateur invité sur un projet.
     */
    public function addParticipation(SocieteUser $societeUser, Projet $projet, string $role): ProjetParticipant
    {
        $participation = ProjetParticipant::create($societeUser, $projet, $role);

        $this->em->persist($participation);

        return $participation;
    }

    /**
     * Vérifie que les champs d'un user sont bien remplis
     * ou que l'email est bien unique
     * avant de terminer l'invitation et d'envoyer l'email.
     *
     * @param mixed $entity Entity to check (User, Societe...)
     * @param FormInterface $form (Optional) Form where to add errors, if not, errors will by thrown.
     *
     * @throws RdiException Si $user n'est pas valide.
     */
    public function check($entity, FormInterface $form = null): void
    {
        $errors = $this->validator->validate($entity, null, ['invitation']);

        if (null !== $form) {
            foreach ($errors as $error) {
                $form->addError(new FormError($error->getMessage()));
            }

            return;
        }

        if ($entity instanceof SocieteUser && $entity->getInvitationEmail()){
            $userSameEmail = $this->em->getRepository(User::class)->findByEmailAndSociete($entity->getSociete(), $entity->getInvitationEmail());

            if (null !== $userSameEmail){
                throw new RdiException('There is already an account with this email');
            }
        }


        if ($errors->count() > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath().': '.$error->getMessage();
            }

            throw new RdiException(sprintf(
                'Erreur lors de l\'invitation, l\'instance a des champs maquants: %s',
                join(', ', $errorMessages)
            ));
        }
    }

    public function sendInvitation(SocieteUser $societeUser, User $from): void
    {
        $this->dispatcher->dispatch(new SocieteUserInvitationNotification($societeUser, $from));

        $societeUser->setInvitationSentAt(new \DateTime());
        $this->em->persist($societeUser);
    }

    public function sendAutomaticInvitationSurSociete(SocieteUser $inviteBy, string $role, string $invitationEmail = null, PhoneNumber $invitationTelephone = null): SocieteUser
    {
        $newSocieteUser = $this->initUser($inviteBy->getSociete());

        if ($invitationEmail !== null){
            $newSocieteUser->setInvitationEmail($invitationEmail);
        } elseif ($invitationTelephone !== null){
            $newSocieteUser->setInvitationTelephone($invitationTelephone);
        } else{
            throw new RdiException('Erreur lors de l\'invitation');
        }

        $newSocieteUser->setRole($role);
        $this->check($newSocieteUser);
        $this->sendInvitation($newSocieteUser, $inviteBy->getUser());
        $this->em->flush();

        return $newSocieteUser;
    }

    public function sendAutomaticInvitationSurProjet(SocieteUser $inviteBy, Projet $projet, string $role, string $invitationEmail): SocieteUser
    {
        $newSocieteUser = $this->initUser($inviteBy->getSociete());
        $newSocieteUser->setInvitationEmail($invitationEmail);
        $this->addParticipation($newSocieteUser, $projet, $role);
        $this->check($newSocieteUser);
        $this->sendInvitation($newSocieteUser, $inviteBy->getUser());

        $this->em->flush();

        return $newSocieteUser;
    }
}
