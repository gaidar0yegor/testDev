<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\TempsPasseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=TempsPasseRepository::class)
 * @UniqueEntity(
 *     fields={"user", "projet", "mois"},
 *     errorPath="pourcentage",
 *     message="Cet utilisateur ne peut pas créer un deuxième pourcentage sur ce projet et ce mois. Il faut modifier l'autre pourcentage plutôt."
 * )
 */
class TempsPasse implements HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"Default", "saisieTemps"})
     */
    private $id;

    /**
     * Pourcentage du temps passe sur le projet
     * De 0 a 100
     *
     * Liste avec un seul entier si même pourcentage pour tout le mois,
     * Liste de plusieurs entiers pour définir un pourcentage chaque jour du mois.
     *
     * @ORM\Column(type="simple_array")
     *
     * @Serializer\Groups({"Default", "saisieTemps"})
     *
     * @var float[]
     */
    private $pourcentages = [0];

    /**
     * Projet sur lequel le temps est passé.
     *
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="tempsPasses")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Serializer\Groups({"Default", "saisieTemps"})
     */
    private $projet;

    /**
     * @ORM\ManyToOne(targetEntity=Cra::class, inversedBy="tempsPasses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cra;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $dayIndex Index of the day, 0 for the first day.
     */
    public function getPourcentage(int $dayIndex = 0): float
    {
        if (1 === count($this->pourcentages)) {
            return $this->pourcentages[0];
        }

        if ($dayIndex >= count($this->pourcentages)) {
            return 0;
        }

        return floatval($this->pourcentages[$dayIndex]);
    }

    public function setPourcentage(float $pourcentage): self
    {
        $this->pourcentages = [$pourcentage];

        return $this;
    }

    public function getPourcentages(): array
    {
        return array_map('floatval', $this->pourcentages);
    }

    public function setPourcentages(array $pourcentages): self
    {
        $this->pourcentages = $pourcentages;

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

    public function getCra(): ?Cra
    {
        return $this->cra;
    }

    public function setCra(?Cra $cra): self
    {
        $this->cra = $cra;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projet->getSociete();
    }
}
