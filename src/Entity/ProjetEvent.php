<?php

namespace App\Entity;

use App\Repository\ProjetEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjetEventRepository::class)
 */
class ProjetEvent
{
    const EVENT_TYPES = [
        'MEETING',
        'EVENT',
        'OTHER'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="projetEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices=self::EVENT_TYPES, message="Choose a valid type.")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=ProjetEventParticipant::class, mappedBy="projetEvent", orphanRemoval=true, cascade={"persist"})
     */
    private $projetEventParticipants;

    public function __construct()
    {
        $this->projetEventParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|ProjetEventParticipant[]
     */
    public function getProjetEventParticipants(): Collection
    {
        return $this->projetEventParticipants;
    }

    public function addProjetEventParticipant(ProjetEventParticipant $projetEventParticipant): self
    {
        if (!$this->projetEventParticipants->contains($projetEventParticipant)) {
            $this->projetEventParticipants[] = $projetEventParticipant;
            $projetEventParticipant->setProjetEvent($this);
        }

        return $this;
    }

    public function removeProjetEventParticipant(ProjetEventParticipant $projetEventParticipant): self
    {
        if ($this->projetEventParticipants->removeElement($projetEventParticipant)) {
            // set the owning side to null (unless already changed)
            if ($projetEventParticipant->getProjetEvent() === $this) {
                $projetEventParticipant->setProjetEvent(null);
            }
        }

        return $this;
    }
}
