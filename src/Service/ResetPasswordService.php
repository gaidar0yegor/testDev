<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\ResetPasswordException;
use App\Repository\UserRepository;

class ResetPasswordService
{
    private TokenGenerator $tokenGenerator;

    private UserRepository $userRepository;

    private RdiMailer $mailer;

    public function __construct(
        TokenGenerator $tokenGenerator,
        UserRepository $userRepository,
        RdiMailer $mailer
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    /**
     * @param string $email De l'utilisateur qui fait la demande.
     *
     * @return User Qui a fait la demande de réinitialisation, et qui a maintenant un token valide.
     *
     * @throws ResetPasswordException Si l'email n'est pas attribué.
     */
    public function requestLink(string $email): User
    {
        $user = $this->userRepository->findOneBy([
            'email' => $email,
            'enabled' => true,
            'invitationToken' => null,
        ]);

        if (null === $user) {
            throw new ResetPasswordException('Cet email n\'est attribué à aucun utilisateur.');
        }

        $token = $this->tokenGenerator->generateUrlToken();
        $expirationDate = (new \DateTime())->modify('+1 day');

        $user
            ->setResetPasswordToken($token)
            ->setResetPasswordTokenExpiresAt($expirationDate)
        ;

        $this->mailer->sendResetPasswordEmail($user);

        return $user;
    }

    public function checkToken(string $token): User
    {
        $user = $this->userRepository->findOneBy([
            'resetPasswordToken' => $token,
            'enabled' => true,
            'invitationToken' => null,
        ]);

        $expiredException = new ResetPasswordException('Ce lien de réinitialisation est expiré ou invalide.');

        if (null === $user) {
            throw $expiredException;
        }

        if (new \DateTime() > $user->getResetPasswordTokenExpiresAt()) {
            throw $expiredException;
        }

        return $user;
    }
}
