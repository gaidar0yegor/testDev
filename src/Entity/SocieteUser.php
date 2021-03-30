<?php

namespace App\Entity;

use App\DTO\NullUser;
use App\HasSocieteInterface;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleSociete;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Représente un utilisateur qui a un rôle sur une société.
 *
 * @ORM\Entity(repositoryClass=SocieteUserRepository::class)
 * @AppAssert\DatesOrdered(
 *      start="dateEntree",
 *      end="dateSortie"
 * )
 */
class SocieteUser implements HasSocieteInterface
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="societeUsers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity=ProjetParticipant::class, mappedBy="societeUser", orphanRemoval=true)
     */
    private $projetParticipants;

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
     * Si cet utilisateur peut accéder à cette société.
     * Si désactivé, l'utilisateur peut se connecter à son compte,
     * mais n'a pas l'accès à cette société.
     *
     * @ORM\Column(type="boolean")
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
     */
    private $invitationEmail;

    /**
     * @ORM\OneToMany(targetEntity=SocieteUserActivity::class, mappedBy="societeUser", orphanRemoval=true)
     */
    private $societeUserActivities;

    /**
     * @ORM\OneToMany(targetEntity=SocieteUserNotification::class, mappedBy="societeUser")
     */
    private $societeUserNotifications;

    public function __construct()
    {
        $this->enabled = true;
        $this->createdAt = new \DateTime();
        $this->cras = new ArrayCollection();
        $this->societeUserActivities = new ArrayCollection();
        $this->societeUserNotifications = new ArrayCollection();
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

    public function removeInvitationToken(): self
    {
        $this->invitationToken = null;
        $this->invitationSentAt = null;
        $this->invitationEmail = null;

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
}
