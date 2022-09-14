<?php

namespace App\Entity;

use App\Entity\LabApp\UserBook;
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
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Représente un compte RDI-Manager d'un utilisateur.
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *      fields={"email"},
 *      groups={"Default", "invitation", "registration"},
 *      message="There is already an account with this email"
 * )
 * @UniqueEntity(
 *      fields={"telephone"},
 *      groups={"Default", "invitation", "registration"},
 *      message="There is already an account with this phone number"
 * )
 * @AppAssert\NotBlankEither(fields={"email", "telephone"})
 */
class User implements UserInterface
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
     *
     * @Groups({"organigramme","comment"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     *
     * @Assert\NotBlank(groups={"registration"})
     *
     * @Groups({"organigramme","comment"})
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Email(mode="strict")
     *
     * @Groups({"organigramme"})
     */
    private $email;

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
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Si cet utilisateur est activé, et peux se connecter à son compte RDI-Manager.
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

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
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $notificationPlanningTaskNotCompletedEnabled;

    /**
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $notificationPlanningTaskStartSoonEnabled;

    /**
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $notificationEvenementInvitationEnabled;

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

    /**
     * All the SocieteUser this user has access to.
     *
     * @ORM\OneToMany(targetEntity=SocieteUser::class, mappedBy="user", cascade={"persist"})
     */
    private $societeUsers;

    /**
     * The current SocieteUser this user has switched to.
     *
     * @ORM\ManyToOne(targetEntity=SocieteUser::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $currentSocieteUser;

    /**
     * @ORM\OneToMany(targetEntity=ProjetObservateurExterne::class, mappedBy="user", orphanRemoval=true)
     */
    private $projetObservateurExternes;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $locale;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist", "remove"})
     *
     * @Groups({"organigramme","comment"})
     */
    private $avatar;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cguCgvAcceptedAt;

    /**
     * @ORM\OneToMany(targetEntity=DashboardConsolide::class, mappedBy="user", orphanRemoval=true)
     */
    private $dashboardConsolides;

    /**
     * @ORM\OneToMany(targetEntity=BoUserNotification::class, mappedBy="boUser", orphanRemoval=true)
     */
    private $boUserNotifications;

    /**
     * @ORM\OneToMany(targetEntity=UserBook::class, mappedBy="user")
     */
    private $userBooks;

    /**
     * The current UserBook this user has switched to.
     *
     * @ORM\ManyToOne(targetEntity=UserBook::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $currentUserBook;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $patchnoteReaded;

    /**
     * @ORM\OneToMany(targetEntity=Rappel::class, mappedBy="user", orphanRemoval=true)
     */
    private $rappels;

    public function __construct()
    {
        $this->enabled = true;
        $this->roles = ['ROLE_FO_USER'];
        $this->projetParticipants = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->notificationEnabled = true;
        $this->notificationCreateFaitMarquantEnabled = true;
        $this->notificationLatestFaitMarquantEnabled = true;
        $this->notificationSaisieTempsEnabled = true;
        $this->notificationPlanningTaskNotCompletedEnabled = true;
        $this->notificationPlanningTaskStartSoonEnabled = true;
        $this->notificationEvenementInvitationEnabled = true;
        $this->onboardingEnabled = true;
        $this->onboardingTimesheetCompleted = false;
        $this->societeUsers = new ArrayCollection();
        $this->projetObservateurExternes = new ArrayCollection();
        $this->locale = 'fr';
        $this->patchnoteReaded = false;
        $this->dashboardConsolides = new ArrayCollection();
        $this->boUserNotifications = new ArrayCollection();
        $this->userBooks = new ArrayCollection();
        $this->rappels = new ArrayCollection();
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
        return $this->email ?? $this->telephone->getNationalNumber();
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function hasResetPasswordToken(): bool
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

    public function getResetPasswordTokenExpiresAt(): ?\DateTime
    {
        return $this->resetPasswordTokenExpiresAt;
    }

    public function setResetPasswordTokenExpiresAt(?\DateTime $resetPasswordTokenExpiresAt): self
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

    /**
     * Get user name abbreviated, like "T. Durden"
     */
    public function getShortname(): string
    {
        if (null === $this->prenom && null === $this->nom) {
            return '-';
        }

        return strtoupper(mb_substr($this->prenom, 0, 1)).'. '.$this->nom;
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

    public function setEmail(?string $email): self
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

    /**
     * @return Collection|SocieteUser[]
     */
    public function getSocieteUsers(): Collection
    {
        return $this->societeUsers;
    }

    /**
     * @return Collection|SocieteUser[]
     */
    public function getEnabledSocieteUsers(): Collection
    {
        return $this->societeUsers->filter(function (SocieteUser $societeUser) {
            return $societeUser->getEnabled();
        });
    }

    public function addSocieteUser(SocieteUser $societeUser): self
    {
        if (!$this->societeUsers->contains($societeUser)) {
            $this->societeUsers[] = $societeUser;
            $societeUser->setUser($this);
        }

        return $this;
    }

    public function removeSocieteUser(SocieteUser $societeUser): self
    {
        if ($this->societeUsers->removeElement($societeUser)) {
            // set the owning side to null (unless already changed)
            if ($societeUser->getUser() === $this) {
                $societeUser->setUser(null);
            }
        }

        return $this;
    }

    public function getCurrentSocieteUser(): ?SocieteUser
    {
        return $this->currentSocieteUser;
    }

    public function setCurrentSocieteUser(?SocieteUser $societeUser): self
    {
        $this->currentSocieteUser = $societeUser;

        return $this;
    }

    /**
     * @return Collection|ProjetObservateurExterne[]
     */
    public function getProjetObservateurExternes(): Collection
    {
        return $this->projetObservateurExternes;
    }

    public function addProjetObservateurExterne(ProjetObservateurExterne $projetParticipant): self
    {
        if (!$this->projetObservateurExternes->contains($projetParticipant)) {
            $this->projetObservateurExternes[] = $projetParticipant;
            $projetParticipant->setUser($this);
        }

        return $this;
    }

    public function removeProjetObservateurExterne(ProjetObservateurExterne $projetParticipant): self
    {
        if ($this->projetObservateurExternes->contains($projetParticipant)) {
            $this->projetObservateurExternes->removeElement($projetParticipant);
            // set the owning side to null (unless already changed)
            if ($projetParticipant->getUser() === $this) {
                $projetParticipant->setUser(null);
            }
        }

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getAvatar(): ?Fichier
    {
        return $this->avatar;
    }

    public function setAvatar(?Fichier $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getCguCgvAcceptedAt(): ?\DateTimeInterface
    {
        return $this->cguCgvAcceptedAt;
    }

    public function setCguCgvAcceptedAt(?\DateTimeInterface $cguCgvAcceptedAt): self
    {
        $this->cguCgvAcceptedAt = $cguCgvAcceptedAt;

        return $this;
    }

    /**
     * @return Collection|DashboardConsolide[]
     */
    public function getDashboardConsolides(): Collection
    {
        return $this->dashboardConsolides;
    }

    public function addDashboardConsolide(DashboardConsolide $dashboardConsolide): self
    {
        if (!$this->dashboardConsolides->contains($dashboardConsolide)) {
            $this->dashboardConsolides[] = $dashboardConsolide;
            $dashboardConsolide->setUser($this);
        }

        return $this;
    }

    public function removeDashboardConsolide(DashboardConsolide $dashboardConsolide): self
    {
        if ($this->dashboardConsolides->removeElement($dashboardConsolide)) {
            // set the owning side to null (unless already changed)
            if ($dashboardConsolide->getUser() === $this) {
                $dashboardConsolide->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BoUserNotification[]
     */
    public function getBoUserNotifications(): Collection
    {
        return $this->boUserNotifications;
    }

    public function addBoUserNotification(BoUserNotification $boUserNotification): self
    {
        if (!$this->boUserNotifications->contains($boUserNotification)) {
            $this->boUserNotifications[] = $boUserNotification;
            $boUserNotification->setBoUser($this);
        }

        return $this;
    }

    public function removeBoUserNotification(BoUserNotification $boUserNotification): self
    {
        if ($this->boUserNotifications->removeElement($boUserNotification)) {
            // set the owning side to null (unless already changed)
            if ($boUserNotification->getBoUser() === $this) {
                $boUserNotification->setBoUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserBook[]
     */
    public function getUserBooks(): Collection
    {
        return $this->userBooks;
    }

    public function addUserBook(UserBook $userBook): self
    {
        if (!$this->userBooks->contains($userBook)) {
            $this->userBooks[] = $userBook;
            $userBook->setUser($this);
        }

        return $this;
    }

    public function removeUserBook(UserBook $userBook): self
    {
        if ($this->userBooks->removeElement($userBook)) {
            // set the owning side to null (unless already changed)
            if ($userBook->getUser() === $this) {
                $userBook->setUser(null);
            }
        }

        return $this;
    }

    public function getCurrentUserBook(): ?UserBook
    {
        return $this->currentUserBook;
    }

    public function setCurrentUserBook(?UserBook $currentUserBook): self
    {
        $this->currentUserBook = $currentUserBook;

        return $this;
    }

    public function getNotificationPlanningTaskNotCompletedEnabled(): ?bool
    {
        return $this->notificationPlanningTaskNotCompletedEnabled;
    }

    public function setNotificationPlanningTaskNotCompletedEnabled(bool $notificationPlanningTaskNotCompletedEnabled): self
    {
        $this->notificationPlanningTaskNotCompletedEnabled = $notificationPlanningTaskNotCompletedEnabled;

        return $this;
    }

    public function getNotificationEvenementInvitationEnabled(): ?bool
    {
        return $this->notificationEvenementInvitationEnabled;
    }

    public function setNotificationEvenementInvitationEnabled(bool $notificationEvenementInvitationEnabled): self
    {
        $this->notificationEvenementInvitationEnabled = $notificationEvenementInvitationEnabled;

        return $this;
    }

    public function getPatchnoteReaded(): ?bool
    {
        return $this->patchnoteReaded;
    }

    public function setPatchnoteReaded(bool $patchnoteReaded): self
    {
        $this->patchnoteReaded = $patchnoteReaded;

        return $this;
    }

    public function getNotificationPlanningTaskStartSoonEnabled(): ?bool
    {
        return $this->notificationPlanningTaskStartSoonEnabled;
    }

    public function setNotificationPlanningTaskStartSoonEnabled(bool $notificationPlanningTaskStartSoonEnabled): self
    {
        $this->notificationPlanningTaskStartSoonEnabled = $notificationPlanningTaskStartSoonEnabled;

        return $this;
    }

    /**
     * @return Collection|Rappel[]
     */
    public function getRappels(): Collection
    {
        return $this->rappels;
    }

    public function addRappel(Rappel $rappel): self
    {
        if (!$this->rappels->contains($rappel)) {
            $this->rappels[] = $rappel;
            $rappel->setUser($this);
        }

        return $this;
    }

    public function removeRappel(Rappel $rappel): self
    {
        if ($this->rappels->removeElement($rappel)) {
            // set the owning side to null (unless already changed)
            if ($rappel->getUser() === $this) {
                $rappel->setUser(null);
            }
        }

        return $this;
    }
}
