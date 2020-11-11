<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\User;
use App\Exception\RdiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service qui s'occupe d'inviter des nouveaux utilisateurs
 * sur RDI manager, en leur envoyant un lien d'invitation.
 * Utilisation :
 *
 * // Init user instance
 * $user = $invitator->initUser();
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
 * // Flush all entities persisted by invitator
 * $em->flush();
 *
 * // Send invitation link
 * $invitator->sendInvitation($user);
 */
class Invitator
{
    private TokenStorageInterface $tokenStorage;

    private TokenGenerator $tokenGenerator;

    private ValidatorInterface $validator;

    private EntityManagerInterface $em;

    private RdiMailer $mailer;

    private FlashBagInterface $flash;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TokenGenerator $tokenGenerator,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        RdiMailer $mailer,
        FlashBagInterface $flash
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
        $this->validator = $validator;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->flash = $flash;
    }

    /**
     * Initialise l'utilisateur qui va être invité.
     */
    public function initUser(): User
    {
        $user = new User();

        $this->em->persist($user);

        return $user
            ->setSociete($this->getLoggedInUser()->getSociete())
            ->setRole('ROLE_FO_USER')
            ->setInvitationToken($this->tokenGenerator->generateUrlToken())
        ;
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
     * avant de terminer l'invitation et d'envoyer l'email.
     *
     * @throws RdiException Si $user n'est pas valide.
     */
    public function check(User $user): void
    {
        $errors = $this->validator->validate($user, null, ['invitation']);

        if ($errors->count() > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath().': '.$error->getMessage();
            }

            throw new RdiException(sprintf(
                'Erreur lors de l\'invitation, l\'instance User a des champs maquants: %s',
                join(', ', $errorMessages)
            ));
        }
    }

    public function sendInvitation(User $user): void
    {
        $this->mailer->sendInvitationEmail($user, $this->getLoggedInUser());

        $this->flash->add('success', sprintf(
            'Un email avec un lien d\'invitation a été envoyé à "%s".',
            $user->getEmail()
        ));
    }

    private function getLoggedInUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
