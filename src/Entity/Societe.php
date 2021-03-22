<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\SocieteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
     * This societe has been created by someone who followed
     * the inscription tunnel (/creer-ma-societe).
     *
     * @param string
     */
    public const CREATED_FROM_INSCRIPTION = 'INSCRIPTION';

    /**
     * This societe has been created by a back office user.
     *
     * @param string
     */
    public const CREATED_FROM_BACK_OFFICE = 'BACK_OFFICE';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     */
    private $uuid;

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
     * @ORM\OneToMany(targetEntity=SocieteUser::class, mappedBy="societe", orphanRemoval=true, cascade={"persist"})
     *
     * @Assert\Valid(groups={"Default", "invitation"})
     */
    private $societeUsers;

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

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * How this Societe has been created (back office, inscription, ...)
     *
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $createdFrom;

    /**
     * Who created this Societe (admin, back office user, ...)
     *
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $createdBy;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->societeUsers = new ArrayCollection();
        $this->heuresParJours = self::DEFAULT_HEURES_PAR_JOURS;
        $this->smsEnabled = true;
        $this->slackAccessTokens = new ArrayCollection();
        $this->projets = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
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
     * @return Collection|SocieteUser[]
     */
    public function getSocieteUsers(): Collection
    {
        return $this->societeUsers;
    }

    public function addSocieteUser(SocieteUser $user): self
    {
        if (!$this->societeUsers->contains($user)) {
            $this->societeUsers[] = $user;
            $user->setSociete($this);
        }

        return $this;
    }

    public function removeSocieteUser(SocieteUser $user): self
    {
        if ($this->societeUsers->contains($user)) {
            $this->societeUsers->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getSociete() === $this) {
                $user->setSociete(null);
            }
        }

        return $this;
    }

    public function getAdmins(): ArrayCollection
    {
        return $this->societeUsers->filter(function (SocieteUser $societeUser) {
            return $societeUser->isAdminFo();
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedFrom(): ?string
    {
        return $this->createdFrom;
    }

    public function setCreatedFrom(?string $createdFrom): self
    {
        $this->createdFrom = $createdFrom;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
