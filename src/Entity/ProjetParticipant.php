<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\ProjetParticipantRepository;
use App\Security\Role\RoleProjet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjetParticipantRepository::class)
 * @UniqueEntity(
 *     fields={"societeUser", "projet"},
 *     errorPath="societeUser",
 *     message="Cet utilisateur a déjà un rôle sur ce projet."
 * )
 */
class ProjetParticipant implements HasSocieteInterface
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
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="projetParticipants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $societeUser;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="projetParticipants")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\Valid
     */
    private $projet;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $role;

    /**
     * The datetime of the last action $societeUser did on $projet (view, update...)
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActionAt;

    /**
     * Whether societeUser is watching this projet,
     * and then want to receive more notifications about updates.
     *
     * @ORM\Column(type="boolean")
     */
    private $watching;

    /**
     * @ORM\ManyToMany(targetEntity=ProjetPlanningTask::class, mappedBy="participants")
     */
    private $projetPlanningTasks;

    public function __construct()
    {
        $this->dateAjout = new \DateTime();
        $this->watching = false;
        $this->projetPlanningTasks = new ArrayCollection();
    }

    public static function create(SocieteUser $societeUser, Projet $projet, ?string $role): self
    {
        $projetParticipant = (new self())->setRole($role);

        $projet->addProjetParticipant($projetParticipant);
        $societeUser->addProjetParticipant($projetParticipant);

        return $projetParticipant;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getSocieteUser(): ?SocieteUser
    {
        return $this->societeUser;
    }

    public function setSocieteUser(?SocieteUser $societeUser): self
    {
        $this->societeUser = $societeUser;

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
        if (null !== $role) {
            RoleProjet::checkRole($role);
        }

        $this->role = $role;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projet->getSociete();
    }

    public function getLastActionAt(): ?\DateTimeInterface
    {
        return $this->lastActionAt;
    }

    public function setLastActionAt(?\DateTimeInterface $lastActionAt): self
    {
        $this->lastActionAt = $lastActionAt;

        return $this;
    }

    public function setLastActionAtNow(): self
    {
        $this->lastActionAt = new \DateTime();

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

    /**
     * @return Collection|ProjetPlanningTask[]
     */
    public function getProjetPlanningTasks(): Collection
    {
        return $this->projetPlanningTasks;
    }

    public function addProjetPlanningTask(ProjetPlanningTask $projetPlanningTask): self
    {
        if (!$this->projetPlanningTasks->contains($projetPlanningTask)) {
            $this->projetPlanningTasks[] = $projetPlanningTask;
            $projetPlanningTask->addParticipant($this);
        }

        return $this;
    }

    public function removeProjetPlanningTask(ProjetPlanningTask $projetPlanningTask): self
    {
        if ($this->projetPlanningTasks->removeElement($projetPlanningTask)) {
            $projetPlanningTask->removeParticipant($this);
        }

        return $this;
    }
}
