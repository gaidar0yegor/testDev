<?php

namespace App\Entity;

use App\DTO\NullUser;
use App\HasSocieteInterface;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleSociete;
use App\UserResourceInterface;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Représente un utilisateur qui a un rôle sur une société.
 *
 * @ORM\Entity(repositoryClass=SocieteUserRepository::class)
 *
 * @AppAssert\NotBlankEither(
 *      fields={"invitationEmail", "invitationTelephone"},
 *      groups={"invitation"}
 * )
 *
 * @UniqueEntity(
 *      fields={"societe","invitationEmail"},
 *      groups={"Default", "invitation", "registration"},
 *      message="There is already an invitation with this email address"
 * )
 *
 * @UniqueEntity(
 *      fields={"societe","invitationTelephone"},
 *      groups={"Default", "invitation", "registration"},
 *      message="There is already an invitation with this phone number"
 * )
 */
class SocieteUser implements HasSocieteInterface, UserResourceInterface
{
    /**
     * L'utilisateur a été invité mais n'a pas encore accépté l'invitation.
     * Le statut est donc en cours d'invitation.
     *
     * @var string
     */
    const STATUT_INVITATION = 'SOCIETE_USER_STATUT_INVITATION';

    /**
     * L'utilisateur dans la société.
     *
     * @var string
     */
    const STATUT_ACTIVE = 'SOCIETE_USER_STATUT_ACTIVE';

    /**
     * L'utilisateur est dans la société, mais son accès à la société
     * a été désactivé par l'admin. Il ne peut plus se connecter
     * tant qu'il n'a pas été réactivé.
     *
     * @var string
     */
    const STATUT_DISABLED = 'SOCIETE_USER_STATUT_DISABLED';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"organigramme"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Societe::class, inversedBy="societeUsers")
     * @ORM\JoinColumn(nullable=true)
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $societe;

    /**
     * Représente le compte RDI-Manager qui a accès à cette société.
     * Peut être null si l'user n'a pas encore rejoint, dans le cas d'une invitation en attente.
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="societeUsers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     *
     * @Groups({"organigramme","comment"})
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=31)
     *
     * @Groups({"organigramme"})
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity=ProjetParticipant::class, mappedBy="societeUser", orphanRemoval=true, cascade={"persist"})
     *
     * @Assert\Valid
     */
    private $projetParticipants;

    /**
     * @ORM\OneToMany(targetEntity=FaitMarquant::class, mappedBy="createdBy", orphanRemoval=true)
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $faitMarquants;

    /**
     * Heures travaillées par jours pour cet employé.
     *
     * @ORM\Column(type="decimal", precision=5, scale=3, nullable=true)
     */
    private $heuresParJours;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/",
     *     match= true,
     *     message="Work start time is invalid"
     *     )
     */
    private $workStartTime;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/",
     *     match= true,
     *     message="Work end time is invalid"
     *     )
     */
    private $workEndTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Si cet utilisateur peut accéder à cette société.
     * Si désactivé, l'utilisateur peut se connecter à son compte,
     * mais n'a pas l'accès à cette société.
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"organigramme"})
     */
    private $enabled;

    /**
     * @ORM\OneToMany(targetEntity=Cra::class, mappedBy="societeUser", orphanRemoval=true)
     */
    private $cras;

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
     * Email sur laquelle l'invitation a été envoyée.
     * Peut être réutilisée pour préremplir l'email de l'utilisateur
     * si il n'a pas encore de compten RDI-Manager et qu'il en crée un.
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
     * @ORM\OneToMany(targetEntity=SocieteUserActivity::class, mappedBy="societeUser", orphanRemoval=true)
     */
    private $societeUserActivities;

    /**
     * @ORM\OneToMany(targetEntity=SocieteUserNotification::class, mappedBy="societeUser", orphanRemoval=true)
     */
    private $societeUserNotifications;

    /**
     * Si l'utilisateur accepte les mails de relance
     *
     * @ORM\Column(type="boolean")
     */
    private $notificationOnboardingEnabled;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $notificationOnboardingLastSentAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationOnboardingFinished;

    /**
     * @ORM\OneToMany(targetEntity=SocieteUserPeriod::class, mappedBy="societeUser", orphanRemoval=true, cascade={"persist"})
     */
    private $societeUserPeriods;

    /**
     * @ORM\ManyToMany(targetEntity=FichierProjet::class, mappedBy="societeUsers", orphanRemoval=true)
     */
    private $fichierProjets;

    /**
     * @ORM\ManyToMany(targetEntity=DashboardConsolide::class, mappedBy="societeUsers", orphanRemoval=true)
     */
    private $dashboardConsolides;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="teamMembers")
     */
    private $mySuperior;

    /**
     * @ORM\OneToMany(targetEntity=SocieteUser::class, mappedBy="mySuperior")
     *
     *
     * @MaxDepth(1)
     * @Groups({"organigramme"})
     */
    private $teamMembers;

    /**
     * @ORM\OneToMany(targetEntity=ProjetPlanning::class, mappedBy="createdBy")
     */
    private $projetPlannings;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $coutEtp;

    /**
     * @ORM\OneToMany(targetEntity=Evenement::class, mappedBy="createdBy", orphanRemoval=true)
     */
    private $createdEvenements;

    /**
     * @ORM\OneToMany(targetEntity=SocieteUserEvenementNotification::class, mappedBy="societeUser", orphanRemoval=true)
     */
    private $societeUserEvenementNotifications;

    /**
     * @ORM\OneToMany(targetEntity=EvenementParticipant::class, mappedBy="societeUser", orphanRemoval=true, cascade={"persist"})
     */
    private $evenementParticipants;

    public function __construct()
    {
        $this->enabled = true;
        $this->createdAt = new \DateTime();
        $this->cras = new ArrayCollection();
        $this->projetParticipants = new ArrayCollection();
        $this->societeUserActivities = new ArrayCollection();
        $this->societeUserNotifications = new ArrayCollection();
        $this->faitMarquants = new ArrayCollection();
        $this->notificationOnboardingEnabled = true;
        $this->notificationOnboardingFinished = false;
        $this->societeUserPeriods = new ArrayCollection();
        $this->fichierProjets = new ArrayCollection();
        $this->dashboardConsolides = new ArrayCollection();
        $this->teamMembers = new ArrayCollection();
        $this->projetPlannings = new ArrayCollection();
        $this->createdEvenements = new ArrayCollection();
        $this->societeUserEvenementNotifications = new ArrayCollection();
        $this->evenementParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        RoleSociete::checkRole($role);

        $this->role = $role;

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
            $projetParticipant->setSocieteUser($this);
        }

        return $this;
    }

    public function removeProjetParticipant(ProjetParticipant $projetParticipant): self
    {
        if ($this->projetParticipants->contains($projetParticipant)) {
            $this->projetParticipants->removeElement($projetParticipant);
            // set the owning side to null (unless already changed)
            if ($projetParticipant->getSocieteUser() === $this) {
                $projetParticipant->setSocieteUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FaitMarquant[]
     */
    public function getFaitMarquants(): Collection
    {
        return $this->faitMarquants;
    }

    public function hasFaitMarquants(): bool
    {
        return count($this->faitMarquants) > 0;
    }

    public function addFaitMarquant(FaitMarquant $faitMarquant): self
    {
        if (!$this->faitMarquants->contains($faitMarquant)) {
            $this->faitMarquants[] = $faitMarquant;
            $faitMarquant->setCreatedBy($this);
        }

        return $this;
    }

    public function removeFaitMarquant(FaitMarquant $faitMarquant): self
    {
        if ($this->faitMarquants->contains($faitMarquant)) {
            $this->faitMarquants->removeElement($faitMarquant);
            if ($faitMarquant->getCreatedBy() === $this) {
                $faitMarquant->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function isAdminFo(): bool
    {
        return RoleSociete::ADMIN === $this->role;
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
            $cra->setSocieteUser($this);
        }

        return $this;
    }

    public function removeCra(Cra $cra): self
    {
        if ($this->cras->contains($cra)) {
            $this->cras->removeElement($cra);
            // set the owning side to null (unless already changed)
            if ($cra->getSocieteUser() === $this) {
                $cra->setSocieteUser(null);
            }
        }

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

    /**
     * @return Collection|SocieteUserActivity[]
     */
    public function getUserActivities(): Collection
    {
        return $this->societeUserActivities;
    }

    public function addSocieteUserActivity(SocieteUserActivity $societeUserActivity): self
    {
        if (!$this->societeUserActivities->contains($societeUserActivity)) {
            $this->societeUserActivities[] = $societeUserActivity;
            $societeUserActivity->setSocieteUser($this);
        }

        return $this;
    }

    public function removeSocieteUserActivity(SocieteUserActivity $societeUserActivity): self
    {
        if ($this->societeUserActivities->removeElement($societeUserActivity)) {
            // set the owning side to null (unless already changed)
            if ($societeUserActivity->getSocieteUser() === $this) {
                $societeUserActivity->setSocieteUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SocieteUserNotification[]
     */
    public function getSocieteUserNotifications(): Collection
    {
        return $this->societeUserNotifications;
    }

    public function addSocieteUserNotification(SocieteUserNotification $userNotification): self
    {
        if (!$this->societeUserNotifications->contains($userNotification)) {
            $this->societeUserNotifications[] = $userNotification;
            $userNotification->setUser($this);
        }

        return $this;
    }

    public function removeSocieteUserNotification(SocieteUserNotification $userNotification): self
    {
        if ($this->societeUserNotifications->removeElement($userNotification)) {
            // set the owning side to null (unless already changed)
            if ($userNotification->getUser() === $this) {
                $userNotification->setUser(null);
            }
        }

        return $this;
    }

    public function getStatut(): string
    {
        if (null === $this->user) {
            return self::STATUT_INVITATION;
        }

        if (!$this->enabled) {
            return self::STATUT_DISABLED;
        }

        return self::STATUT_ACTIVE;
    }

    public function getNotificationOnboardingEnabled(): ?bool
    {
        return $this->notificationOnboardingEnabled;
    }

    public function setNotificationOnboardingEnabled(bool $notificationOnboardingEnabled): self
    {
        $this->notificationOnboardingEnabled = $notificationOnboardingEnabled;

        return $this;
    }

    public function getNotificationOnboardingLastSentAt(): ?\DateTimeInterface
    {
        return $this->notificationOnboardingLastSentAt;
    }

    public function setNotificationOnboardingLastSentAt(?\DateTimeInterface $notificationOnboardingLastSentAt): self
    {
        $this->notificationOnboardingLastSentAt = $notificationOnboardingLastSentAt;

        return $this;
    }

    public function getNotificationOnboardingFinished(): ?bool
    {
        return $this->notificationOnboardingFinished;
    }

    public function setNotificationOnboardingFinished(bool $notificationOnboardingFinished): self
    {
        $this->notificationOnboardingFinished = $notificationOnboardingFinished;

        return $this;
    }

    /**
     * @return Collection|SocieteUserPeriod[]
     */
    public function getSocieteUserPeriods(): Collection
    {
        return $this->societeUserPeriods;
    }

    public function getLastSocieteUserPeriod(): SocieteUserPeriod
    {
        foreach ( array_reverse($this->societeUserPeriods->toArray()) as $societeUserPeriod ) {
            if ($societeUserPeriod->getDateEntry() !== null || $societeUserPeriod->getDateLeave() !== null){
                return $societeUserPeriod;
            }
        }
        return $this->societeUserPeriods->last();
    }

    public function addSocieteUserPeriod(SocieteUserPeriod $societeUserPeriod): self
    {
        if (!$this->societeUserPeriods->contains($societeUserPeriod)) {
            $this->societeUserPeriods[] = $societeUserPeriod;
            $societeUserPeriod->setSocieteUser($this);
        }

        return $this;
    }

    public function removeSocieteUserPeriod(SocieteUserPeriod $societeUserPeriod): self
    {
        if ($this->societeUserPeriods->removeElement($societeUserPeriod)) {
            // set the owning side to null (unless already changed)
            if ($societeUserPeriod->getSocieteUser() === $this) {
                $societeUserPeriod->setSocieteUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FichierProjet[]
     */
    public function getFichierProjets(): Collection
    {
        return $this->fichierProjets;
    }

    public function addFichierProjet(FichierProjet $fichierProjet): self
    {
        if (!$this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets[] = $fichierProjet;
            $fichierProjet->addSocieteUser($this);
        }

        return $this;
    }

    public function removeFichierProjet(FichierProjet $fichierProjet): self
    {
        if ($this->fichierProjets->removeElement($fichierProjet)) {
            $fichierProjet->removeSocieteUser($this);
        }

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
            $dashboardConsolide->addSocieteUser($this);
        }

        return $this;
    }

    public function removeDashboardConsolide(DashboardConsolide $dashboardConsolide): self
    {
        if ($this->dashboardConsolides->removeElement($dashboardConsolide)) {
            $dashboardConsolide->removeSocieteUser($this);
        }

        return $this;
    }

    public function getMySuperior(): ?self
    {
        return $this->mySuperior;
    }

    public function setMySuperior(?self $mySuperior): self
    {
        $this->mySuperior = $mySuperior;

        return $this;
    }

    public function isSuperiorFo(): bool
    {
        return $this->getTeamMembers()->count() > 0;
    }

    /**
     * je suis N.
     * Retourner les N-1
     * @return Collection|self[]
     */
    public function getTeamMembers(): Collection
    {
        return $this->teamMembers;
    }

    /**
     * je suis N.
     * Ajouter un N-1
     */
    public function addTeamMember(self $teamMember): self
    {
        if (!$this->teamMembers->contains($teamMember)) {
            $this->teamMembers[] = $teamMember;
            $teamMember->setMySuperior($this);
        }

        return $this;
    }

    /**
     * je suis N.
     * supprimer un N-1
     */
    public function removeTeamMember(self $teamMember): self
    {
        if ($this->teamMembers->removeElement($teamMember)) {
            // set the owning side to null (unless already changed)
            if ($teamMember->getMySuperior() === $this) {
                $teamMember->setMySuperior(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProjetPlanning[]
     */
    public function getProjetPlannings(): Collection
    {
        return $this->projetPlannings;
    }

    public function addProjetPlanning(ProjetPlanning $projetPlanning): self
    {
        if (!$this->projetPlannings->contains($projetPlanning)) {
            $this->projetPlannings[] = $projetPlanning;
            $projetPlanning->setCreatedBy($this);
        }

        return $this;
    }

    public function removeProjetPlanning(ProjetPlanning $projetPlanning): self
    {
        if ($this->projetPlannings->removeElement($projetPlanning)) {
            // set the owning side to null (unless already changed)
            if ($projetPlanning->getCreatedBy() === $this) {
                $projetPlanning->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getCoutEtp(): ?string
    {
        return $this->coutEtp;
    }

    public function setCoutEtp(?string $coutEtp): self
    {
        $this->coutEtp = $coutEtp;

        return $this;
    }

    /**
     * @return Collection|Evenement[]
     */
    public function getCreatedEvenements(): Collection
    {
        return $this->createdEvenements;
    }

    public function addCreatedEvenement(Evenement $createdEvenement): self
    {
        if (!$this->createdEvenements->contains($createdEvenement)) {
            $this->createdEvenements[] = $createdEvenement;
            $createdEvenement->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedEvenement(Evenement $createdEvenement): self
    {
        if ($this->createdEvenements->removeElement($createdEvenement)) {
            // set the owning side to null (unless already changed)
            if ($createdEvenement->getCreatedBy() === $this) {
                $createdEvenement->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SocieteUserEvenementNotification[]
     */
    public function getSocieteUserEvenementNotifications(): Collection
    {
        return $this->societeUserEvenementNotifications;
    }

    public function addSocieteUserEvenementNotification(SocieteUserEvenementNotification $societeUserEvenementNotification): self
    {
        if (!$this->societeUserEvenementNotifications->contains($societeUserEvenementNotification)) {
            $this->societeUserEvenementNotifications[] = $societeUserEvenementNotification;
            $societeUserEvenementNotification->setSocieteUser($this);
        }

        return $this;
    }

    public function removeSocieteUserEvenementNotification(SocieteUserEvenementNotification $societeUserEvenementNotification): self
    {
        if ($this->societeUserEvenementNotifications->removeElement($societeUserEvenementNotification)) {
            // set the owning side to null (unless already changed)
            if ($societeUserEvenementNotification->getSocieteUser() === $this) {
                $societeUserEvenementNotification->setSocieteUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EvenementParticipant[]
     */
    public function getEvenementParticipants(): Collection
    {
        return $this->evenementParticipants;
    }

    /**
     * @param int|null $limit
     * @return Collection|EvenementParticipant[]
     */
    public function getNextEvenementParticipants(int $limit = null): Collection
    {
        $iterator = $this->evenementParticipants->filter(function (EvenementParticipant $evenementParticipant) {
            return $evenementParticipant->getEvenement()->getStartDate()->getTimestamp() >= (new \DateTime())->getTimestamp();
        })->getIterator();

        $iterator->uasort(function (EvenementParticipant $evenementParticipant1 , EvenementParticipant $evenementParticipant2){
            return $evenementParticipant1->getEvenement()->getStartDate() > $evenementParticipant2->getEvenement()->getStartDate() ? 1 : -1;
        });

        $collection = new ArrayCollection(iterator_to_array($iterator));
        $collection = $limit !== null ? new ArrayCollection($collection->slice(0, $limit)) : $collection;

        return $collection;
    }

    /**
     * @param int|null $limit
     * @return Collection|EvenementParticipant[]
     */
    public function getOldEvenementParticipants(int $limit = null): Collection
    {
        $iterator = $this->evenementParticipants->filter(function (EvenementParticipant $evenementParticipant) {
            return $evenementParticipant->getEvenement()->getStartDate()->getTimestamp() < (new \DateTime())->getTimestamp();
        })->getIterator();

        $iterator->uasort(function (EvenementParticipant $evenementParticipant1 , EvenementParticipant $evenementParticipant2){
            return $evenementParticipant1->getEvenement()->getStartDate() < $evenementParticipant2->getEvenement()->getStartDate() ? 1 : -1;
        });

        $collection = new ArrayCollection(iterator_to_array($iterator));
        $collection = $limit !== null ? new ArrayCollection($collection->slice(0, $limit)) : $collection;

        return $collection;
    }

    /**
     * @param Evenement $evenement
     * @return bool
     */
    public function isInvitedToEvenement(Evenement $evenement): bool
    {
        return $this->evenementParticipants->filter(function (EvenementParticipant $evenementParticipant) use ($evenement){
                return $evenementParticipant->getEvenement() === $evenement;
            })->count() > 0;
    }

    public function addEvenementParticipant(EvenementParticipant $evenementParticipant): self
    {
        if (!$this->evenementParticipants->contains($evenementParticipant)) {
            $this->evenementParticipants[] = $evenementParticipant;
            $evenementParticipant->setSocieteUser($this);
        }

        return $this;
    }

    public function removeEvenementParticipant(EvenementParticipant $evenementParticipant): self
    {
        if ($this->evenementParticipants->removeElement($evenementParticipant)) {
            // set the owning side to null (unless already changed)
            if ($evenementParticipant->getSocieteUser() === $this) {
                $evenementParticipant->setSocieteUser(null);
            }
        }

        return $this;
    }

    public function getWorkStartTime(): ?string
    {
        return $this->workStartTime;
    }

    public function setWorkStartTime(?string $workStartTime): self
    {
        $this->workStartTime = $workStartTime;

        return $this;
    }

    public function getWorkEndTime(): ?string
    {
        return $this->workEndTime;
    }

    public function setWorkEndTime(?string $workEndTime): self
    {
        $this->workEndTime = $workEndTime;

        return $this;
    }
}
