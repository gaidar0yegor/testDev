<?php

namespace App\Security\UserProvider;

use App\Entity\User;
use App\Repository\UserRepository;
use InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Allows to login with either email or phone number.
 */
class LoginUserWithEmailOrPhone implements UserProviderInterface
{
    private UserRepository $userRepository;

    private PhoneNumberUtil $phoneNumberUtil;

    public function __construct(UserRepository $userRepository, PhoneNumberUtil $phoneNumberUtil)
    {
        $this->userRepository = $userRepository;
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function loadUserByUsername(string $usernameOrPhone)
    {
        if (str_contains($usernameOrPhone, '@')) {
            $user = $this->userRepository->findOneByEmail($usernameOrPhone);
        } else {
            try {
                $phoneNumber = $this->phoneNumberUtil->parse($usernameOrPhone);
                $user = $this->userRepository->findOneByTelephone($phoneNumber);
            } catch (NumberParseException $e) {
                // If invalid phone number, let authentication fail
            }
        }

        if (null === $user) {
            throw new UsernameNotFoundException('Aucun utilisateur avec cet email ou numéro de téléphone.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException();
        }

        $userId = $user->getId();

        if (!$userId) {
            throw new InvalidArgumentException('User has no id, it may have been changed in session');
        }

        $user = $this->userRepository->find($userId);

        if (null === $user) {
            throw new UsernameNotFoundException('Cet utilisateur n\'est plus en base.');
        }

        return $user;
    }

    public function supportsClass(string $class)
    {
        return $class === User::class;
    }
}
