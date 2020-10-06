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
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="participantsProjets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=Projets::class, inversedBy="participants_projet")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projets;

    /**
     * @ORM\ManyToOne(targetEntity=RolesParticipantProjet::class, inversedBy="participantsProjets")
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

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getProjets(): ?Projets
    {
        return $this->projets;
    }

    public function setProjets(?Projets $projets): self
    {
        $this->projets = $projets;

        return $this;
    }

    public function getRolesParticipantProjet(): ?RolesParticipantProjet
    {
        return $this->roles_participant_projet;
    }

    public function setRolesParticipantProjet(?RolesParticipantProjet $roles_participant_projet): self
    {
        $this->roles_participant_projet = $roles_participant_projet;

        return $this;
    }
}
