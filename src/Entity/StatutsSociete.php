<?php

namespace App\Entity;

use App\Repository\StatutSocieteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatutSocieteRepository::class)
 */
class StatutsSociete
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=Societes::class, mappedBy="statuts_societe")
     */
    private $societes;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->societes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection|Societes[]
     */
    public function getSocietes(): Collection
    {
        return $this->societes;
    }

    public function addSociete(Societes $societe): self
    {
        if (!$this->societes->contains($societe)) {
            $this->societes[] = $societe;
            $societe->setStatutsSociete($this);
        }

        return $this;
    }

    public function removeSociete(Societes $societe): self
    {
        if ($this->societes->contains($societe)) {
            $this->societes->removeElement($societe);
            // set the owning side to null (unless already changed)
            if ($societe->getStatutsSociete() === $this) {
                $societe->setStatutsSociete(null);
            }
        }

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
