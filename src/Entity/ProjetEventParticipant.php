<?php

namespace App\Entity;

use App\Repository\ProjetEventParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetEventParticipantRepository::class)
 */
class ProjetEventParticipant
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accepted;

    public static function create(ProjetEvent $projetEvent, ProjetParticipant $participant): self
    {
        return (new self())
            ->setProjetEvent($projetEvent)
            ->setParticipant($participant)
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

    public function getAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(?bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }
}
