<?php

namespace App\Entity;

use App\Repository\RoleParticipantProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoleParticipantProjetRepository::class)
 */
class RoleParticipantProjet
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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
            $participantsProjet->setRoleParticipantProjet($this);
        }

        return $this;
    }

    public function removeParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if ($this->participantsProjets->contains($participantsProjet)) {
            $this->participantsProjets->removeElement($participantsProjet);
            if ($participantsProjet->getRoleParticipantProjet() === $this) {
                $participantsProjet->setRoleParticipantProjet(null);
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
