<?php

namespace App\Entity;

use App\Repository\StatutsRdiDuProjetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatutsRdiDuProjetRepository::class)
 */
class StatutsRdiDuProjet
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
    private $libelle_statut_rdi_projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelleStatutRdiProjet(): ?string
    {
        return $this->libelle_statut_rdi_projet;
    }

    public function setLibelleStatutRdiProjet(string $libelle_statut_rdi_projet): self
    {
        $this->libelle_statut_rdi_projet = $libelle_statut_rdi_projet;

        return $this;
    }
}
