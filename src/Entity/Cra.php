<?php

namespace App\Entity;

use App\Repository\CraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Détermine quels jours un utilisateur a travaillé sur un mois donné.
 *
 * @ORM\Entity(repositoryClass=CraRepository::class)
 * @UniqueEntity(
 *     fields={"user", "mois"},
 *     errorPath="mois",
 *     message="Cet utilisateur a déjà soumis un CRA sur ce mois. Il faut modifier l'autre CRA plutôt."
 * )
 */
class Cra
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cras")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="date")
     */
    private $mois;

    /**
     * Tableau contenant une liste de '1', '0' ou '0.5'
     * correspondant aux jours travaillés dans le mois.
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @var float[]
     */
    private $jours = [];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $craModifiedAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $tempsPassesModifiedAt;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="cra", orphanRemoval=true, cascade={"persist"})
     */
    private $tempsPasses;

    public function __construct()
    {
        $this->tempsPasses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMois(): ?\DateTimeInterface
    {
        return $this->mois;
    }

    public function setMois(\DateTimeInterface $mois): self
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * @return float[]
     */
    public function getJours(): ?array
    {
        return array_map('floatval', $this->jours);
    }

    /**
     * @param float[] $jours
     *
     * @return self
     */
    public function setJours(?array $jours): self
    {
        $this->jours = $jours;

        return $this;
    }

    public function getCraModifiedAt(): ?\DateTimeInterface
    {
        return $this->craModifiedAt;
    }

    public function setCraModifiedAt(\DateTimeInterface $craModifiedAt): self
    {
        $this->craModifiedAt = $craModifiedAt;

        return $this;
    }

    public function getTempsPassesModifiedAt(): ?\DateTimeInterface
    {
        return $this->tempsPassesModifiedAt;
    }

    public function setTempsPassesModifiedAt(\DateTimeInterface $tempsPassesModifiedAt): self
    {
        $this->tempsPassesModifiedAt = $tempsPassesModifiedAt;

        return $this;
    }

    /**
     * @return Collection|TempsPasse[]
     */
    public function getTempsPasses(): Collection
    {
        return $this->tempsPasses;
    }

    public function hasTempsPasses(): bool
    {
        return count($this->tempsPasses) > 0;
    }

    public function addTempsPass(TempsPasse $tempsPasse): self
    {
        if (!$this->tempsPasses->contains($tempsPasse)) {
            $this->tempsPasses[] = $tempsPasse;
            $tempsPasse->setCra($this);
        }

        return $this;
    }

    public function removeTempsPass(TempsPasse $tempsPasse): self
    {
        if ($this->tempsPasses->contains($tempsPasse)) {
            $this->tempsPasses->removeElement($tempsPasse);
            // set the owning side to null (unless already changed)
            if ($tempsPasse->getCra() === $this) {
                $tempsPasse->setCra(null);
            }
        }

        return $this;
    }

    public function isCraSubmitted(): bool
    {
        if (null === $this->id) {
            return false;
        }

        return null !== $this->craModifiedAt;
    }

    public function isTempsPassesSubmitted(): bool
    {
        foreach ($this->tempsPasses as $tempsPasse) {
            if (null === $tempsPasse->getId()) {
                return false;
            }
        }

        return null !== $this->tempsPassesModifiedAt;
    }

    /**
     * Vérifie qu'une liste de pourcentage de temps passés est valide.
     *
     * @Assert\Callback
     *
     * @param TempsPasse[] $tempsPasses
     *
     * @throws TempsPassesPercentException Si les pourcentages ne sont pas valide.
     */
    public function checkPercents(ExecutionContextInterface $context, $payload)
    {
        $totalPercent = 0;

        foreach ($this->tempsPasses as $tempsPasse) {
            $percent = $tempsPasse->getPourcentage();

            if ($percent < 0 || $percent > 100) {
                $context
                    ->buildViolation(sprintf(
                        'Un pourcentage doit être entre 0 et 100, %d obtenu.',
                        $percent
                    ))
                    ->addViolation()
                ;
            }

            $totalPercent += $percent;
        }

        if ($totalPercent < 0 || $totalPercent > 100) {
            $context
                ->buildViolation(sprintf(
                    'La somme des pourcentages doit être entre 0 et 100, %d obtenu.',
                    $totalPercent
                ))
                ->addViolation()
            ;
        }
    }
}
