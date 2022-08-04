<?php

namespace App\RegisterLabo;

use App\Entity\LabApp\Labo;
use App\Entity\LabApp\UserBook;
use App\RegisterSociete\DTO\Registration;
use App\Security\Role\RoleLabo;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegisterUserBook
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
            ->subject('[RDI-Manager] Code de vérification : Création d\'un cahier de laboratoire')
            ->textTemplate('mail/register-verification-code.txt.twig')
            ->htmlTemplate('mail/register-verification-code.html.twig')
            ->context([
                'verificationCode' => $registration->verificationCode,
            ])
        ;
    }

    public function persistRegistrationUserBook(Registration $registration): UserBook
    {
        $userBook = $registration->userBook;
        $admin = $registration->admin;

        $admin->addUserBook($userBook);

        $this->em->persist($admin);
        $this->em->persist($userBook);

        return $userBook;
    }

    public function persistRegistrationLabo(Registration $registration): Labo
    {
        $userBook = $registration->userBook;
        $labo = $registration->labo;

        if (null === $labo->getId()){
            $userBook->setRole(RoleLabo::ADMIN);
            $labo
                ->setCreatedBy($userBook->getUser())
                ->addUserBook($userBook)
            ;
        } else {
            $labo
                ->addUserBook($userBook)
            ;
        }

        $this->em->persist($labo);
        $this->em->persist($userBook);

        return $labo;
    }
}
