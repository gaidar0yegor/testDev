<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\EvenementParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EvenementParticipantRepository::class)
 */
class EvenementParticipant implements HasSocieteInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Evenement::class, inversedBy="evenementParticipants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $evenement;

    /**
     * @ORM\Column(type="boolean")
     */
    private $required;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="evenementParticipants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $societeUser;

    /**
     * @ORM\Column(type="json")
     */
    private $heures = [];

    public static function create(Evenement $evenement, SocieteUser $societeUser, bool $required): self
    {
        return (new self())
            ->setEvenement($evenement)
            ->setSocieteUser($societeUser)
            ->setRequired($required)
            ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->evenement->getSociete();
    }

    public function getOwner(): SocieteUser
    {
        return $this->societeUser;
    }

    public function getProjet(): ?Projet
    {
        return $this->evenement->getProjet();
    }

    public function getRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

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

    public function getHeures(): ?array
    {
        return $this->heures;
    }

    public function setHeures(array $heures): self
    {
        $this->heures = $heures;

        return $this;
    }
}
