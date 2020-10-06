<?php

namespace App\Entity;

use App\Repository\JoursAbsenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JoursAbsenceRepository::class)
 */
class JoursAbsence
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
    private $date_jour;

    /**
     * @ORM\Column(type="boolean")
     */
    private $journee_entiere;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="joursAbsences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateJour(): ?\DateTimeInterface
    {
        return $this->date_jour;
    }

    public function setDateJour(\DateTimeInterface $date_jour): self
    {
        $this->date_jour = $date_jour;

        return $this;
    }

    public function getJourneeEntiere(): ?bool
    {
        return $this->journee_entiere;
    }

    public function setJourneeEntiere(bool $journee_entiere): self
    {
        $this->journee_entiere = $journee_entiere;

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
}
