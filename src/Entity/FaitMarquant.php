<?php

namespace App\Entity;

use App\HasSocieteInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\ProjetResourceInterface;
use App\Repository\FaitMarquantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FaitMarquantRepository::class)
 */
class FaitMarquant implements ProjetResourceInterface, HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="text")
     *  @Assert\Length(
     *      max = 751,
     *      maxMessage = "Votre fait marquant ne peut avoir plus de 750 caractères"
     * )
     */
    private $description;

    /**
     * Date du fait marquant
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="faitMarquants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\OneToMany(targetEntity=FichierProjet::class, mappedBy="faitMarquant", cascade={"all"})
     */
    private $fichierProjets;

    public function __construct()
    {
        $this->fichierProjets = new ArrayCollection();
        $this->createdAt = new \DateTime();
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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
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

    public function getOwner(): User
    {
        return $this->createdBy;
    }

    /**
     * @return Collection|FichierProjet[]
     */
    public function getFichierProjets(): Collection
    {
        return $this->fichierProjets;
    }

    public function setFichierProjets($fichierProjets): self
    {
        $this->fichierProjets = $fichierProjets;

        foreach ($fichierProjets as $fichierProjet) {
            $fichierProjet->setFaitMarquant($this);
        }

        return $this;
    }

    public function addFichierProjet(FichierProjet $fichierProjet): self
    {
        if (!$this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets[] = $fichierProjet;
            $fichierProjet->setFaitMarquant($this);
        }

        return $this;
    }

    public function removeFichierProjet(FichierProjet $fichierProjet): self
    {
        if ($this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets->removeElement($fichierProjet);
            // set the owning side to null (unless already changed)
            if ($fichierProjet->getFaitMarquant() === $this) {
                $fichierProjet->setFaitMarquant(null);
            }
        }

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projet->getSociete();
    }
}
