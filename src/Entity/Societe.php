<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\SocieteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SocieteRepository::class)
 */
class Societe implements HasSocieteInterface
{
    /**
     * Nombre d'heures par jours défini par défaut
     * lorsque une société est créée.
     *
     * @param float
     */
    public const DEFAULT_HEURES_PAR_JOURS = 7.5;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $raisonSociale;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $siret;

    /**
     * Heures travaillées par jours par défaut par les employés.
     *
     * @ORM\Column(type="decimal", precision=5, scale=3, nullable=true)
     */
    private $heuresParJours;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="societe", orphanRemoval=true, cascade={"persist"})
     *
     * @Assert\Valid(groups={"Default", "invitation"})
     */
    private $users;

    /**
     * Utilise ou non les SMS pour les notifications importantes.
     *
     * @ORM\Column(type="boolean")
     */
    private $smsEnabled;

    /**
     * @ORM\OneToMany(targetEntity=SlackAccessToken::class, mappedBy="societe")
     */
    private $slackAccessTokens;

    /**
     * @ORM\OneToMany(targetEntity=Projet::class, mappedBy="societe")
     */
    private $projets;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->heuresParJours = self::DEFAULT_HEURES_PAR_JOURS;
        $this->smsEnabled = true;
        $this->slackAccessTokens = new ArrayCollection();
        $this->projets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): self
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getHeuresParJours(): ?float
    {
        return $this->heuresParJours;
    }

    public function setHeuresParJours(?float $heuresParJours): self
    {
        $this->heuresParJours = $heuresParJours;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setSociete($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getSociete() === $this) {
                $user->setSociete(null);
            }
        }

        return $this;
    }

    public function getAdmins(): ArrayCollection
    {
        return $this->users->filter(function (User $user) {
            return $user->isAdminFo();
        });
    }

    public function getSmsEnabled(): ?bool
    {
        return $this->smsEnabled;
    }

    public function setSmsEnabled(bool $smsEnabled): self
    {
        $this->smsEnabled = $smsEnabled;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this;
    }

    /**
     * @return Collection|SlackAccessToken[]
     */
    public function getSlackAccessTokens(): Collection
    {
        return $this->slackAccessTokens;
    }

    public function addSlackAccessToken(SlackAccessToken $slackAccessToken): self
    {
        if (!$this->slackAccessTokens->contains($slackAccessToken)) {
            $this->slackAccessTokens[] = $slackAccessToken;
            $slackAccessToken->setSociete($this);
        }

        return $this;
    }

    public function removeSlackAccessToken(SlackAccessToken $slackAccessToken): self
    {
        if ($this->slackAccessTokens->removeElement($slackAccessToken)) {
            // set the owning side to null (unless already changed)
            if ($slackAccessToken->getSociete() === $this) {
                $slackAccessToken->setSociete(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Projet[]
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets[] = $projet;
            $projet->setSociete($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        if ($this->projets->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getSociete() === $this) {
                $projet->setSociete(null);
            }
        }

        return $this;
    }
}
