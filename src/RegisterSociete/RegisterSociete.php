<?php

namespace App\RegisterSociete;

use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\RegisterSociete\DTO\Registration;
use App\Security\Role\RoleSociete;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegisterSociete
{
    private const SESSION_KEY_NAME = 'currentRegistration';

    private SessionInterface $session;

    private TokenGenerator $tokenGenerator;

    private EntityManagerInterface $em;

    public function __construct(
        SessionInterface $session,
        TokenGenerator $tokenGenerator,
        EntityManagerInterface $em
    ) {
        $this->session = $session;
        $this->tokenGenerator = $tokenGenerator;
        $this->em = $em;
    }

    public function initializeCurrentRegistration(): Registration
    {
        $registration = new Registration();

        $this->setCurrentRegistration($registration);

        return $registration;
    }

    public function hasCurrentRegistration(): bool
    {
        return $this->session->has(self::SESSION_KEY_NAME);
    }

    public function getCurrentRegistration(): Registration
    {
        return $this->session->get(self::SESSION_KEY_NAME);
    }

    public function setCurrentRegistration(Registration $registration): void
    {
        $this->session->set(self::SESSION_KEY_NAME, $registration);
    }

    public function updateVerificationCode(Registration $registration): void
    {
        $registration->verificationCode = $this->tokenGenerator->generate6DigitVerificationCode();
    }

    public function createVerificationCodeEmail(Registration $registration): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->to($registration->admin->getEmail())
            ->subject('[RDI-Manager] Code de vérification Journal collaboratif')
            ->textTemplate('mail/register-verification-code.txt.twig')
            ->htmlTemplate('mail/register-verification-code.html.twig')
            ->context([
                'verificationCode' => $registration->verificationCode,
            ])
        ;
    }

    public function persistRegistration(Registration $registration): SocieteUser
    {
        $societe = $registration->societe;
        $admin = $registration->admin;
        $societeUser = new SocieteUser();

        $societe
            ->setCreatedFrom(Societe::CREATED_FROM_INSCRIPTION)
            ->setCreatedBy($admin)
            ->addSocieteUser($societeUser)
        ;

        $admin
            ->addSocieteUser($societeUser)
        ;

        $societeUser
            ->setRole(RoleSociete::ADMIN)
            ->addSocieteUserPeriod(new SocieteUserPeriod())
        ;

        $this->em->persist($admin);
        $this->em->persist($societe);
        $this->em->persist($societeUser);

        return $societeUser;
    }
}
