<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\ProjetResourceInterface;
use App\Repository\ProjetEventParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetEventParticipantRepository::class)
 */
class ProjetEventParticipant implements ProjetResourceInterface, HasSocieteInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ProjetEvent::class, inversedBy="projetEventParticipants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projetEvent;

    /**
     * @ORM\ManyToOne(targetEntity=ProjetParticipant::class, inversedBy="projetEventParticipants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $participant;

    /**
     * @ORM\Column(type="boolean")
     */
    private $required;

    public static function create(ProjetEvent $projetEvent, ProjetParticipant $participant, bool $required): self
    {
        return (new self())
            ->setProjetEvent($projetEvent)
            ->setParticipant($participant)
            ->setRequired($required)
            ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjetEvent(): ?ProjetEvent
    {
        return $this->projetEvent;
    }

    public function setProjetEvent(?ProjetEvent $projetEvent): self
    {
        $this->projetEvent = $projetEvent;

        return $this;
    }

    public function getParticipant(): ?ProjetParticipant
    {
        return $this->participant;
    }

    public function setParticipant(?ProjetParticipant $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projetEvent->getSociete();
    }

    public function getOwner(): SocieteUser
    {
        return $this->participant->getSocieteUser();
    }

    public function getProjet(): Projet
    {
        return $this->projetEvent->getProjet();
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
}
