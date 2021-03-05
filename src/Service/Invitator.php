<?php

namespace App\Service;

use App\DTO\InitSociete;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\User;
use App\Exception\RdiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
 * $invitator->addParticipation($user, $projet, Role::CONTRIBUTEUR);
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

    private RdiMailer $mailer;

    public function __construct(
        TokenGenerator $tokenGenerator,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        SocieteInitializer $societeInitializer,
        RdiMailer $mailer
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->validator = $validator;
        $this->em = $em;
        $this->societeInitializer = $societeInitializer;
        $this->mailer = $mailer;
    }

    /**
     * Initialise l'utilisateur qui va être invité.
     */
    public function initUser(Societe $societe, string $role = 'ROLE_FO_USER'): User
    {
        $user = new User();

        $this->em->persist($user);

        return $user
            ->setSociete($societe)
            ->setRole($role)
            ->setInvitationToken($this->tokenGenerator->generateUrlToken())
        ;
    }

    public function initSociete(InitSociete $initSociete): Societe
    {
        return $this->societeInitializer->initializeSociete($initSociete);
    }

    /**
     * Ajoute l'utilisateur invité sur un projet.
     */
    public function addParticipation(User $user, Projet $projet, string $role): ProjetParticipant
    {
        $participation = new ProjetParticipant();

        $this->em->persist($participation);

        return $participation
            ->setUser($user)
            ->setRole($role)
            ->setProjet($projet)
        ;
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

    public function sendInvitation(User $user, User $from): void
    {
        $this->mailer->sendInvitationEmail($user, $from);

        $user->setInvitationSentAt(new \DateTime());
        $this->em->persist($user);
    }
}
