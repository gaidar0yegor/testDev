<?php

namespace App\Entity\LabApp;

use App\Entity\User;
use App\HasUserBookInterface;
use App\Repository\LabApp\UserBookRepository;
use App\Security\Role\RoleLabo;
use Doctrine\ORM\Mapping as ORM;
use App\DTO\NullUser;
use App\UserResourceInterface;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserBookRepository::class)
 *
 * @AppAssert\NotBlankEither(
 *      fields={"invitationEmail", "invitationTelephone"},
 *      groups={"invitation"}
 * )
 */
class UserBook implements UserResourceInterface, HasUserBookInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Labo::class, inversedBy="userBooks")
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $labo;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userBooks", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $invitationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
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
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="createdBy")
     */
    private $notes;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity=Etude::class, mappedBy="userBook", orphanRemoval=true)
     */
    private $etudes;

    public function __construct()
    {
        $this->role = RoleLabo::USER;
        $this->createdAt = new \DateTime();
        $this->notes = new ArrayCollection();
        $this->etudes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function getUserBook(): ?UserBook
    {
        return $this;
    }

    public function hasUser(): bool
    {
        return null !== $this->user;
    }

    public function getUser(): User
    {
        if (null === $this->user) {
            return new NullUser($this);
        }

        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getInvitationToken(): ?string
    {
        return $this->invitationToken;
    }

    public function setInvitationToken(?string $invitationToken): self
    {
        $this->invitationToken = $invitationToken;

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

    public function getInvitationSentAt(): ?\DateTimeInterface
    {
        return $this->invitationSentAt;
    }

    public function setInvitationSentAt(?\DateTimeInterface $invitationSentAt): self
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

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setCreatedBy($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getCreatedBy() === $this) {
                $note->setCreatedBy(null);
            }
        }

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

    /**
     * @return Collection|Etude[]
     */
    public function getEtudes(): Collection
    {
        return $this->etudes;
    }

    public function addEtude(Etude $etude): self
    {
        if (!$this->etudes->contains($etude)) {
            $this->etudes[] = $etude;
            $etude->setUserBook($this);
        }

        return $this;
    }

    public function removeEtude(Etude $etude): self
    {
        if ($this->etudes->removeElement($etude)) {
            // set the owning side to null (unless already changed)
            if ($etude->getUserBook() === $this) {
                $etude->setUserBook(null);
            }
        }

        return $this;
    }
}
