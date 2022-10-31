<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\ProjetResourceInterface;
use App\Repository\ProjetPlanningTaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ProjetPlanningTaskRepository::class)
 */
class ProjetPlanningTask implements ProjetResourceInterface, HasSocieteInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ProjetPlanning::class, inversedBy="projetPlanningTasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projetPlanning;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="float")
     */
    private $progress;

    /**
     * @ORM\ManyToOne(targetEntity=ProjetPlanningTask::class, inversedBy="children")
     */
    private $parentTask;

    /**
     * @ORM\OneToMany(targetEntity=ProjetPlanningTask::class, mappedBy="parentTask", orphanRemoval=true)
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity=FaitMarquant::class, mappedBy="projetPlanningTask")
     * @ORM\OrderBy({"date" = "DESC", "id" = "DESC"})
     */
    private $faitMarquants;

    /**
     * @ORM\ManyToMany(targetEntity=ProjetParticipant::class, inversedBy="projetPlanningTasks")
     */
    private $participants;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDateReal;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->duration = 1;
        $this->progress = 0;
        $this->faitMarquants = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): string
    {
        $now = new \DateTime();

        if ($now < $this->startDate) {
            return 'upcoming';
        }

        if ($this->progress < 1) {
            return 'in_progress';
        }

        return 'ended';
    }

    public function getProjetPlanning(): ?ProjetPlanning
    {
        return $this->projetPlanning;
    }

    public function setProjetPlanning(?ProjetPlanning $projetPlanning): self
    {
        $this->projetPlanning = $projetPlanning;

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

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getProgress(): ?float
    {
        return $this->progress;
    }

    public function setProgress(float $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    public function getParentTask(): ?self
    {
        return $this->parentTask;
    }

    public function setParentTask(?self $parentTask): self
    {
        $this->parentTask = $parentTask;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getAllLevelsChildren(): Collection
    {
        $allLevelsChildren = new ArrayCollection();
        foreach ($this->children as $child){
            $allLevelsChildren->add($child);
            foreach ($child->getChildren() as $subChild){
                $allLevelsChildren->add($subChild);
            }
        }
        return $allLevelsChildren;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParentTask($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParentTask() === $this) {
                $child->setParentTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FaitMarquant[]
     */
    public function getFaitMarquants(): Collection
    {
        return $this->faitMarquants;
    }

    public function addFaitMarquant(FaitMarquant $faitMarquant): self
    {
        if (!$this->faitMarquants->contains($faitMarquant)) {
            $this->faitMarquants[] = $faitMarquant;
            $faitMarquant->setProjetPlanningTask($this);
        }

        return $this;
    }

    public function removeFaitMarquant(FaitMarquant $faitMarquant): self
    {
        if ($this->faitMarquants->removeElement($faitMarquant)) {
            // set the owning side to null (unless already changed)
            if ($faitMarquant->getProjetPlanningTask() === $this) {
                $faitMarquant->setProjetPlanningTask(null);
            }
        }

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projetPlanning->getProjet()->getSociete();
    }

    public function getOwner(): SocieteUser
    {
        return $this->projetPlanning->getProjet()->getChefDeProjet();
    }

    public function getProjet(): Projet
    {
        return $this->projetPlanning->getProjet();
    }

    /**
     * @return Collection|ProjetParticipant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ProjetParticipant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(ProjetParticipant $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getEndDateReal(): ?\DateTimeInterface
    {
        return $this->endDateReal;
    }

    public function setEndDateReal(?\DateTimeInterface $endDateReal): self
    {
        $this->endDateReal = $endDateReal;

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
