<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\ResetPasswordException;
use App\Notification\Event\ResetPasswordRequestNotification;
use App\Repository\UserRepository;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResetPasswordService
{
    private TokenGenerator $tokenGenerator;

    private UserRepository $userRepository;

    private PhoneNumberUtil $phoneNumberUtil;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        TokenGenerator $tokenGenerator,
        UserRepository $userRepository,
        PhoneNumberUtil $phoneNumberUtil,
        EventDispatcherInterface $dispatcher
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->userRepository = $userRepository;
        $this->phoneNumberUtil = $phoneNumberUtil;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $username Email ou SMS de l'utilisateur.
     *
     * @return User Qui a fait la demande de réinitialisation, et qui a maintenant un token valide.
     *
     * @throws ResetPasswordException Si l'email n'est pas attribué.
     */
    public function requestLink(string $username): User
    {
        if (str_contains($username, '@')) {
            $user = $this->userRepository->findOneBy([
                'email' => $username,
            ]);
        } else {
            try {
                $phoneNumber = $this->phoneNumberUtil->parse($username);
            } catch (NumberParseException $e) {
                throw new ResetPasswordException('Ce numéro de téléphone semble incorrect.', $e);
            }

            $user = $this->userRepository->findOneBy([
                'telephone' => $phoneNumber,
            ]);
        }

        if (null === $user) {
            throw new ResetPasswordException('Cet email ou n° de téléphone n\'est attribué à aucun utilisateur.');
        }

        if (!$user->getEnabled()) {
            throw new ResetPasswordException('Ce compte est désactivé.');
        }

        $token = $this->tokenGenerator->generateUrlToken();
        $expirationDate = (new \DateTime())->modify('+1 day');

        $user
            ->setResetPasswordToken($token)
            ->setResetPasswordTokenExpiresAt($expirationDate)
        ;

        $this->dispatcher->dispatch(new ResetPasswordRequestNotification($user));

        return $user;
    }

    public function checkToken(string $token): User
    {
        $user = $this->userRepository->findOneBy([
            'resetPasswordToken' => $token,
            'enabled' => true,
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
