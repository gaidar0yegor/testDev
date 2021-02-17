<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\UserRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *      fields={"email"},
 *      groups={"Default", "invitation", "registration"},
 *      message="There is already an account with this email"
 * )
 * @AppAssert\DatesOrdered(
 *      start="dateEntree",
 *      end="dateSortie"
 * )
 */
class User implements UserInterface, HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     *
     * @Assert\NotBlank
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     *
     * @Assert\NotBlank(groups={"registration"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     *
     * @Assert\NotBlank(groups={"registration"})
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * Clé secrète créée lorsque cet user est invité
     * à rejoindre la société et à finaliser la création de son compte.
     *
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
     * Clé secrète créée lorsque cet user souhaite réinitialiser son mot de passe.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetPasswordToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resetPasswordTokenExpiresAt;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(type="mobile", defaultRegion="FR")
     */
    private $telephone;

    /**
     * Heures travaillées par jours pour cet employé.
     *
     * @ORM\Column(type="decimal", precision=5, scale=3, nullable=true)
     */
    private $heuresParJours;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateEntree;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateSortie;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Si cet utilisateur est activé, et peux se connecter.
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\ManyToOne(targetEntity=Societe::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $societe;

    /**
     * @ORM\OneToMany(targetEntity=ProjetParticipant::class, mappedBy="user", orphanRemoval=true)
     */
    private $projetParticipants;

    /**
     * @ORM\OneToMany(targetEntity=Cra::class, mappedBy="user", orphanRemoval=true)
     */
    private $cras;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationEnabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationCreateFaitMarquantEnabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationLatestFaitMarquantEnabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationSaisieTempsEnabled;

    /**
     * @ORM\OneToMany(targetEntity=UserActivity::class, mappedBy="user", orphanRemoval=true)
     */
    private $userActivities;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onboardingEnabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onboardingTimesheetCompleted;

    /**
     * Help texts to display, not yet acknowlegded.
     * Null means user not yet logged in, so display all.
     * Empty array means all help texts have been acknowledged.
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $helpTexts;

    public function __construct()
    {
        $this->enabled = true;
        $this->projetParticipants = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->cras = new ArrayCollection();
        $this->notificationEnabled = true;
        $this->notificationCreateFaitMarquantEnabled = true;
        $this->notificationLatestFaitMarquantEnabled = true;
        $this->notificationSaisieTempsEnabled = true;
        $this->userActivities = new ArrayCollection();
        $this->onboardingEnabled = true;
        $this->onboardingTimesheetCompleted = false;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->email;
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

    public function setInvitationSentAt(?\DateTimeInterface $invitationSentAt): self
    {
        $this->invitationSentAt = $invitationSentAt;

        return $this;
    }

    public function removeInvitationToken(): self
    {
        $this->invitationToken = null;
        $this->invitationSentAt = null;

        return $this;
    }

    public function getResetPasswordToken(): string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function hasResetPasswordToken(): string
    {
        return null !== $this->resetPasswordToken
            && new \DateTime() < $this->resetPasswordTokenExpiresAt
        ;
    }

    public function removeResetPasswordToken(): self
    {
        $this->resetPasswordToken = null;
        $this->resetPasswordTokenExpiresAt = null;

        return $this;
    }

    public function getResetPasswordTokenExpiresAt(): \DateTime
    {
        return $this->resetPasswordTokenExpiresAt;
    }

    public function setResetPasswordTokenExpiresAt(\DateTime $resetPasswordTokenExpiresAt): self
    {
        $this->resetPasswordTokenExpiresAt = $resetPasswordTokenExpiresAt;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string Role front office de l'utilisateur
     */
    public function getRole(): ?string
    {
        $foRoles = array_filter($this->roles, function (string $role) {
            return 'ROLE_FO_' === substr($role, 0, 8);
        });

        if (count($foRoles) > 0) {
            return array_pop($foRoles);
        }

        return null;
    }

    public function isAdminFo(): bool
    {
        return in_array('ROLE_FO_ADMIN', $this->roles);
    }

    public function setRole(string $role): self
    {
        $this->roles = [$role];

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getFullname(): string
    {
        if (null === $this->prenom && null === $this->nom) {
            return '-';
        }

        return $this->prenom . ' ' . $this->nom;
    }

    public function getFullnameOrEmail(): string
    {
        if (null === $this->prenom && null === $this->nom) {
            return $this->email;
        }

        return $this->prenom . ' ' . $this->nom;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?PhoneNumber
    {
        return $this->telephone;
    }

    public function setTelephone(?PhoneNumber $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getHeuresParJours(): ?float
    {
        return $this->heuresParJours;
    }

    public function setHeuresParJours(?float $heuresParJours): self
    {
        $this->heuresParJours = $heuresParJours;

        return $this;
    }

    public function getDateEntree(): ?\DateTimeInterface
    {
        return $this->dateEntree;
    }

    public function setDateEntree(?\DateTimeInterface $dateEntree): self
    {
        $this->dateEntree = $dateEntree;

        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->dateSortie;
    }

    public function setDateSortie(?\DateTimeInterface $dateSortie): self
    {
        $this->dateSortie = $dateSortie;

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

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(?Societe $societe): self
    {
        $this->societe = $societe;

        return $this;
    }

    /**
     * @return Collection|ProjetParticipant[]
     */
    public function getProjetParticipants(): Collection
    {
        return $this->projetParticipants;
    }

    public function addProjetParticipant(ProjetParticipant $projetParticipant): self
    {
        if (!$this->projetParticipants->contains($projetParticipant)) {
            $this->projetParticipants[] = $projetParticipant;
            $projetParticipant->setUser($this);
        }

        return $this;
    }

    public function removeProjetParticipant(ProjetParticipant $projetParticipant): self
    {
        if ($this->projetParticipants->contains($projetParticipant)) {
            $this->projetParticipants->removeElement($projetParticipant);
            // set the owning side to null (unless already changed)
            if ($projetParticipant->getUser() === $this) {
                $projetParticipant->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cra[]
     */
    public function getCras(): Collection
    {
        return $this->cras;
    }

    public function addCra(Cra $cra): self
    {
        if (!$this->cras->contains($cra)) {
            $this->cras[] = $cra;
            $cra->setUser($this);
        }

        return $this;
    }

    public function removeCra(Cra $cra): self
    {
        if ($this->cras->contains($cra)) {
            $this->cras->removeElement($cra);
            // set the owning side to null (unless already changed)
            if ($cra->getUser() === $this) {
                $cra->setUser(null);
            }
        }

        return $this;
    }

    public function getNotificationEnabled(): ?bool
    {
        return $this->notificationEnabled;
    }

    public function setNotificationEnabled(bool $notificationEnabled): self
    {
        $this->notificationEnabled = $notificationEnabled;

        return $this;
    }

    public function getNotificationCreateFaitMarquantEnabled(): ?bool
    {
        return $this->notificationCreateFaitMarquantEnabled;
    }

    public function setNotificationCreateFaitMarquantEnabled(bool $notificationCreateFaitMarquantEnabled): self
    {
        $this->notificationCreateFaitMarquantEnabled = $notificationCreateFaitMarquantEnabled;

        return $this;
    }

    public function getNotificationLatestFaitMarquantEnabled(): ?bool
    {
        return $this->notificationLatestFaitMarquantEnabled;
    }

    public function setNotificationLatestFaitMarquantEnabled(bool $notificationLatestFaitMarquantEnabled): self
    {
        $this->notificationLatestFaitMarquantEnabled = $notificationLatestFaitMarquantEnabled;

        return $this;
    }

    public function getNotificationSaisieTempsEnabled(): ?bool
    {
        return $this->notificationSaisieTempsEnabled;
    }

    public function setNotificationSaisieTempsEnabled(bool $notificationSaisieTempsEnabled): self
    {
        $this->notificationSaisieTempsEnabled = $notificationSaisieTempsEnabled;

        return $this;
    }

    /**
     * @return Collection|UserActivity[]
     */
    public function getUserActivities(): Collection
    {
        return $this->userActivities;
    }

    public function addUserActivity(UserActivity $userActivity): self
    {
        if (!$this->userActivities->contains($userActivity)) {
            $this->userActivities[] = $userActivity;
            $userActivity->setUser($this);
        }

        return $this;
    }

    public function removeUserActivity(UserActivity $userActivity): self
    {
        if ($this->userActivities->removeElement($userActivity)) {
            // set the owning side to null (unless already changed)
            if ($userActivity->getUser() === $this) {
                $userActivity->setUser(null);
            }
        }

        return $this;
    }

    public function getOnboardingEnabled(): ?bool
    {
        return $this->onboardingEnabled;
    }

    public function setOnboardingEnabled(bool $onboardingEnabled): self
    {
        $this->onboardingEnabled = $onboardingEnabled;

        return $this;
    }

    public function getOnboardingTimesheetCompleted(): ?bool
    {
        return $this->onboardingTimesheetCompleted;
    }

    public function setOnboardingTimesheetCompleted(bool $onboardingTimesheetCompleted): self
    {
        $this->onboardingTimesheetCompleted = $onboardingTimesheetCompleted;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getHelpTexts(): ?array
    {
        return $this->helpTexts;
    }

    /**
     * @param string[] $helpTexts
     */
    public function setHelpTexts(?array $helpTexts): self
    {
        $this->helpTexts = $helpTexts;

        return $this;
    }
}
