<?php

namespace App\Entity;

use App\Repository\RappelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity(repositoryClass=RappelRepository::class)
 */
class Rappel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $rappelDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $minutesToReminde;

    /**
     * @ORM\Column(type="datetime")
     */
    private $reminderAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="rappels")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Ignore
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Societe::class)
     *
     * @Ignore
     */
    private $societe;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $isReminded;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $acknowledged;

    public function __construct()
    {
        $this->rappelDate = new \DateTime();
        $this->minutesToReminde = 0;
        $this->isReminded = false;
        $this->acknowledged = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

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

    public function getReminderAt(): ?\DateTimeInterface
    {
        return $this->reminderAt;
    }

//    /**
//     * @ORM\PrePersist
//     */
//    public function prePersistReminderAt(): void
//    {
//        $this->reminderAt = $this->getRappelDate()
//    }

    public function setReminderAt(\DateTimeInterface $reminderAt): self
    {
        $this->reminderAt = $reminderAt;

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

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(?Societe $societe): self
    {
        $this->societe = $societe;

        return $this;
    }

    public function getRappelDate(): ?\DateTimeInterface
    {
        return $this->rappelDate;
    }

    public function setRappelDate(\DateTimeInterface $rappelDate): self
    {
        $this->rappelDate = $rappelDate;

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

    public function getIsReminded(): ?bool
    {
        return $this->isReminded;
    }

    public function setIsReminded(bool $isReminded): self
    {
        $this->isReminded = $isReminded;

        return $this;
    }

    public function getAcknowledged(): ?bool
    {
        return $this->acknowledged;
    }

    public function setAcknowledged(bool $acknowledged): self
    {
        $this->acknowledged = $acknowledged;

        return $this;
    }
}
