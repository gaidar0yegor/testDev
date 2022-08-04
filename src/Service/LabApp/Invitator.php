<?php

namespace App\Service\LabApp;

use App\Entity\LabApp\Labo;
use App\Entity\LabApp\UserBookInvite;
use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\UserBookInvitationNotification;
use App\Security\Role\RoleLabo;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Invitator
{
    private TokenGenerator $tokenGenerator;

    private ValidatorInterface $validator;

    private EntityManagerInterface $em;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        TokenGenerator $tokenGenerator,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->validator = $validator;
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Initialise l'utilisateur qui va être invité.
     */
    public function initUserBookInvite(Labo $labo, string $role = RoleLabo::USER): UserBookInvite
    {
        $userBookInvite = new UserBookInvite();

        $this->em->persist($userBookInvite);

        $userBookInvite
            ->setLabo($labo)
            ->setRole($role)
            ->setInvitationToken($this->tokenGenerator->generateUrlToken());

        return $userBookInvite;
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

    public function sendInvitation(UserBookInvite $userBookInvitation, User $from): void
    {
        $this->dispatcher->dispatch(new UserBookInvitationNotification($userBookInvitation, $from));

        $userBookInvitation->setInvitationSentAt(new \DateTime());
        $this->em->persist($userBookInvitation);
    }
}
