<?php

namespace App\Entity;

use App\Repository\ParticipantsProjetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParticipantsProjetRepository::class)
 */
class ParticipantsProjet
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
    private $date_ajout;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="participantsProjets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="participantsProjet")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projets;

    /**
     * @ORM\ManyToOne(targetEntity=RoleParticipantProjet::class, inversedBy="participantsProjets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $roles_participant_projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDateAjout(\DateTimeInterface $date_ajout): self
    {
        $this->date_ajout = $date_ajout;

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

    public function getProjets(): ?Projet
    {
        return $this->projets;
    }

    public function setProjets(?Projet $projets): self
    {
        $this->projets = $projets;

        return $this;
    }

    public function getRoleParticipantProjet(): ?RoleParticipantProjet
    {
        return $this->roles_participant_projet;
    }

    public function setRoleParticipantProjet(?RoleParticipantProjet $roles_participant_projet): self
    {
        $this->roles_participant_projet = $roles_participant_projet;

        return $this;
    }
}
