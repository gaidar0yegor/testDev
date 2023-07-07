<?php

namespace App\Entity\LabApp;

use App\Repository\LabApp\UserBookInviteRepository;
use App\Security\Role\RoleLabo;
use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

/**
 * @ORM\Entity(repositoryClass=UserBookInviteRepository::class)
 *
 * @AppAssert\NotBlankEither(
 *      fields={"invitationEmail", "invitationTelephone"},
 *      groups={"invitation"}
 * )
 */
class UserBookInvite
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $invitationToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $invitationSentAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Email(mode="strict")
     */
    private $invitationEmail;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(type="mobile", defaultRegion="FR")
     */
    private $invitationTelephone;

    /**
     * @ORM\ManyToOne(targetEntity=Labo::class, inversedBy="userBookInvites")
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $labo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $role;

    public function __construct()
    {
        $this->role = RoleLabo::USER;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInvitationSentAt(): ?\DateTimeInterface
    {
        return $this->invitationSentAt;
    }

    public function setInvitationSentAt(\DateTimeInterface $invitationSentAt): self
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

    public function getInvitationTelephone()
    {
        return $this->invitationTelephone;
    }

    public function setInvitationTelephone($invitationTelephone): self
    {
        $this->invitationTelephone = $invitationTelephone;

        return $this;
    }

    public function getLabo(): ?Labo
    {
        return $this->labo;
    }

    public function setLabo(?Labo $labo): self
    {
        $this->labo = $labo;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }
}
