<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EvenementRepository::class)
 */
class Evenement implements HasSocieteInterface
{
    const TYPE_MEETING = 'MEETING';
    const TYPE_EVENT = 'EVENT';
    const TYPE_ABSENCE = 'ABSENCE';
    const TYPE_PERSONAL = 'PERSONAL';
    const TYPE_OTHER = 'OTHER';

    const EVENEMENT_TYPES = [ ...self::SOCIETE_USER_EVENEMENT_TYPES, ...self::PROJET_EVENEMENT_TYPES];

    const PROJET_EVENEMENT_TYPES = [
        self::TYPE_MEETING,
        self::TYPE_EVENT,
        self::TYPE_OTHER
    ];

    const SOCIETE_USER_EVENEMENT_TYPES = [
        self::TYPE_ABSENCE,
        self::TYPE_PERSONAL,
    ];

    private const EVENEMENT_TYPES_UPDATE_CRA = [self::TYPE_ABSENCE];

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
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices=self::EVENEMENT_TYPES, message="Choose a valid type.")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=EvenementParticipant::class, mappedBy="evenement", orphanRemoval=true, cascade={"persist"})
     */
    private $evenementParticipants;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $externalParticipantEmails = [];

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="createdEvenements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="evenements")
     */
    private $projet;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $autoUpdateCra;

    /**
     * @ORM\Column(type="integer")
     */
    private $minutesToReminde;

    /**
     * @ORM\Column(type="datetime")
     */
    private $reminderAt;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $isReminded;

    public function __construct()
    {
        $this->evenementParticipants = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->type = self::TYPE_MEETING;
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

    public function setDescription(?string $description): self
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
     * @return Collection|EvenementParticipant[]
     */
    public function getEvenementParticipants(): Collection
    {
        return $this->evenementParticipants;
    }

    /**
     * @return Collection|EvenementParticipant[]
     */
    public function getRequiredEvenementParticipants(): Collection
    {
        return $this->evenementParticipants->filter(function (EvenementParticipant $evenementParticipant){
            return $evenementParticipant->getRequired() === true;
        });
    }

    /**
     * @return Collection|EvenementParticipant[]
     */
    public function getNotRequiredEvenementParticipants(): Collection
    {
        return $this->evenementParticipants->filter(function (EvenementParticipant $evenementParticipant){
            return $evenementParticipant->getRequired() === false;
        });
    }

    public function addEvenementParticipant(EvenementParticipant $evenementParticipant): self
    {
        if (!$this->evenementParticipants->contains($evenementParticipant)) {
            $this->evenementParticipants[] = $evenementParticipant;
            $evenementParticipant->setEvenement($this);
        }

        return $this;
    }

    public function removeEvenementParticipant(EvenementParticipant $evenementParticipant): self
    {
        if ($this->evenementParticipants->removeElement($evenementParticipant)) {
            // set the owning side to null (unless already changed)
            if ($evenementParticipant->getEvenement() === $this) {
                $evenementParticipant->setEvenement(null);
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?SocieteUser
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?SocieteUser $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->createdBy->getSociete();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

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

    public function getAutoUpdateCra(): ?bool
    {
        return $this->autoUpdateCra && in_array($this->type, self::EVENEMENT_TYPES_UPDATE_CRA);
    }

    public function setAutoUpdateCra(bool $autoUpdateCra): self
    {
        $this->autoUpdateCra = $autoUpdateCra;

        return $this;
    }

    public function getExternalParticipantEmails(): ?array
    {
        return $this->externalParticipantEmails;
    }

    public function setExternalParticipantEmails(?array $externalParticipantEmails): self
    {
        $this->externalParticipantEmails = $externalParticipantEmails;

        return $this;
    }

    public function getMinutesToReminde(): ?int
    {
        return $this->minutesToReminde;
    }

    public function setMinutesToReminde(int $minutesToReminde): self
    {
        $this->minutesToReminde = $minutesToReminde;

        return $this;
    }

    public function getReminderAt(): ?\DateTimeInterface
    {
        return $this->reminderAt;
    }

    public function setReminderAt(\DateTimeInterface $reminderAt): self
    {
        $this->reminderAt = $reminderAt;

        return $this;
    }

    public function getIsReminded(): ?bool
    {
        return $this->isReminded;
    }

    public function setIsReminded(bool $isReminded): self
    {
        $this->isReminded = $isReminded;

        return $this;
    }
}
