<?php

namespace App\Entity;

use App\Repository\RolesParticipantProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RolesParticipantProjetRepository::class)
 */
class RolesParticipantProjet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=ParticipantsProjet::class, mappedBy="roles_participant_projet")
     */
    private $participantsProjets;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->participantsProjets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelleRoleParticipantProjet(): ?string
    {
        return $this->libelle_role_participant_projet;
    }

    public function setLibelleRoleParticipantProjet(string $libelle_role_participant_projet): self
    {
        $this->libelle_role_participant_projet = $libelle_role_participant_projet;

        return $this;
    }

    /**
     * @return Collection|ParticipantsProjet[]
     */
    public function getParticipantsProjets(): Collection
    {
        return $this->participantsProjets;
    }

    public function addParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if (!$this->participantsProjets->contains($participantsProjet)) {
            $this->participantsProjets[] = $participantsProjet;
            $participantsProjet->setRolesParticipantProjet($this);
        }

        return $this;
    }

    public function removeParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if ($this->participantsProjets->contains($participantsProjet)) {
            $this->participantsProjets->removeElement($participantsProjet);
            // set the owning side to null (unless already changed)
            if ($participantsProjet->getRolesParticipantProjet() === $this) {
                $participantsProjet->setRolesParticipantProjet(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
