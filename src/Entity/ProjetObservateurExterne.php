<?php

namespace App\Entity;

use App\Repository\ProjetObservateurExterneRepository;
use App\Security\Role\RoleProjet;
use App\Validator as AppAssert;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Donne à un utilisateur externe
 * (i.e pas forcement dans la société)
 * un accès observateur sur un projet.
 *
 * @ORM\Entity(repositoryClass=ProjetObservateurExterneRepository::class)
 * @UniqueEntity(
 *     fields={"user", "projet"},
 *     errorPath="user",
 *     message="Cet utilisateur a déjà un rôle sur ce projet."
 * )
 * @AppAssert\NotBlankEither(
 *      fields={"invitationEmail", "invitationTelephone"},
 *      groups={"invitation"}
 * )
 */
class ProjetObservateurExterne
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $dateAjout;

    /**
     * Null until someone accept invitation to oberve the projet.
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projetObservateurExternes")
     * @ORM\JoinColumn(nullable=true)
     *
     * @Groups({"comment"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="projetObservateurExternes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * The datetime of the last action $societeUser did on $projet (view, update...)
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActionAt;

    /**
     * Clé secrète créée lorsque cet user est invité
     * en tant qu'observateur externe sur ce projet.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $invitationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $invitationSentAt;

    /**
     * Email sur laquelle l'invitation a été envoyée.
     * Peut être réutilisée pour préremplir l'email de l'utilisateur
     * si il n'a pas encore de compte RDI-Manager et qu'il en crée un.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Email(mode="strict")
     */
    private $invitationEmail;

    /**
     * Numéro de mobile sur laquelle l'invitation a été envoyée.
     * Peut être réutilisée pour préremplir le téléphone de l'utilisateur
     * si il n'a pas encore de compten RDI-Manager et qu'il en crée un.
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(type="mobile", defaultRegion="FR")
     */
    private $invitationTelephone;

    /**
     * Whether societeUser is watching this projet,
     * and then want to receive more notifications about updates.
     *
     * @ORM\Column(type="boolean")
     */
    private $watching;

    public function __construct()
    {
        $this->dateAjout = new DateTime();
        $this->watching = false;
    }

    public static function create(User $user, Projet $projet, string $role): self
    {
        return (new self())
            ->setUser($user)
            ->setProjet($projet)
            ->setRole($role)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAjout(): ?DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        RoleProjet::checkRole($role);

        $this->role = $role;

        return $this;
    }

    public function getLastActionAt(): ?DateTimeInterface
    {
        return $this->lastActionAt;
    }

    public function setLastActionAt(?DateTimeInterface $lastActionAt): self
    {
        $this->lastActionAt = $lastActionAt;

        return $this;
    }

    public function setLastActionAtNow(): self
    {
        $this->lastActionAt = new DateTime();

        return $this;
    }

    public function getInvitationToken(): ?string
    {
        return $this->invitationToken;
    }

    public function setInvitationToken(string $invitationToken): self
    {
        $this->invitationToken = $invitationToken;

        return $this;
    }

    public function getInvitationSentAt(): ?DateTimeInterface
    {
        return $this->invitationSentAt;
    }

    public function setInvitationSentAt(?DateTimeInterface $invitationSentAt): self
    {
        $this->invitationSentAt = $invitationSentAt;

        return $this;
    }

    public function getInvitationEmail(): ?string
    {
        return $this->invitationEmail;
    }

    public function setInvitationEmail(?string $invitationEmail): self
    {
        $this->invitationEmail = $invitationEmail;

        return $this;
    }

    public function getInvitationTelephone(): ?PhoneNumber
    {
        return $this->invitationTelephone;
    }

    public function setInvitationTelephone(?PhoneNumber $invitationTelephone): self
    {
        $this->invitationTelephone = $invitationTelephone;

        return $this;
    }

    public function removeInvitationToken(): self
    {
        $this->invitationToken = null;
        $this->invitationSentAt = null;
        $this->invitationEmail = null;
        $this->invitationTelephone = null;

        return $this;
    }

    public function getWatching(): bool
    {
        return $this->watching;
    }

    public function setWatching(bool $watching): self
    {
        $this->watching = $watching;

        return $this;
    }
}
