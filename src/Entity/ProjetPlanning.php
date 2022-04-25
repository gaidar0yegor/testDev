<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\ProjetResourceInterface;
use App\Repository\ProjetPlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ProjetPlanningRepository::class)
 */
class ProjetPlanning implements ProjetResourceInterface, HasSocieteInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Projet::class, inversedBy="projetPlanning", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=ProjetPlanningTask::class, mappedBy="projetPlanning", orphanRemoval=true, cascade={"persist", "remove"})
     * @Serializer\Groups({"gantt"})
     */
    private $projetPlanningTasks;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="projetPlannings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $efficacite;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $effectivite;

    public function __construct()
    {
        $this->projetPlanningTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjet(): Projet
    {
        return $this->projet;
    }

    public function setProjet(Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|ProjetPlanningTask[]
     */
    public function getProjetPlanningTasks(): Collection
    {
        return $this->projetPlanningTasks;
    }

    public function addProjetPlanningTask(ProjetPlanningTask $projetPlanningTask): self
    {
        if (!$this->projetPlanningTasks->contains($projetPlanningTask)) {
            $this->projetPlanningTasks[] = $projetPlanningTask;
            $projetPlanningTask->setProjetPlanning($this);
        }

        return $this;
    }

    public function removeProjetPlanningTask(ProjetPlanningTask $projetPlanningTask): self
    {
        if ($this->projetPlanningTasks->removeElement($projetPlanningTask)) {
            // set the owning side to null (unless already changed)
            if ($projetPlanningTask->getProjetPlanning() === $this) {
                $projetPlanningTask->setProjetPlanning(null);
            }
        }

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projet->getSociete();
    }

    public function getOwner(): SocieteUser
    {
        return $this->projet->getChefDeProjet();
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

    public function getEfficacite(): ?string
    {
        return $this->efficacite;
    }

    public function setEfficacite(?string $efficacite): self
    {
        $this->efficacite = $efficacite;

        return $this;
    }

    public function getEffectivite(): ?string
    {
        return $this->effectivite;
    }

    public function setEffectivite(?string $effectivite): self
    {
        $this->effectivite = $effectivite;

        return $this;
    }
}
