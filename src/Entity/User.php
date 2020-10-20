<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $deletedBy;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cadre;

    /**
     * @ORM\OneToOne(targetEntity=Licences::class, inversedBy="user", cascade={"persist", "remove"})
     */
    private $licences;

    /**
     * @ORM\ManyToOne(targetEntity=Societes::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $societes;

    /**
     * @ORM\ManyToOne(targetEntity=ProfilsUtilisateur::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $profils_utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=StatutsUtilisateur::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $statuts_utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=BaseTempsParContrat::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $base_temps_par_contrat;

    /**
     * @ORM\OneToMany(targetEntity=JoursAbsence::class, mappedBy="users", orphanRemoval=true)
     */
    private $joursAbsences;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="user", orphanRemoval=true)
     */
    private $tempsPasses;

    /**
     * @ORM\OneToMany(targetEntity=ProjetParticipant::class, mappedBy="user", orphanRemoval=true)
     */
    private $projetParticipants;

    public function __construct()
    {
        $this->joursAbsences = new ArrayCollection();
        $this->tempsPasses = new ArrayCollection();
        $this->projetParticipants = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->id;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedBy(): ?string
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?string $deletedBy): self
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    public function getCadre(): ?bool
    {
        return $this->cadre;
    }

    public function setCadre(bool $cadre): self
    {
        $this->cadre = $cadre;

        return $this;
    }

    public function getLicences(): ?Licences
    {
        return $this->licences;
    }

    public function setLicences(?Licences $licences): self
    {
        $this->licences = $licences;

        return $this;
    }

    public function getSocietes(): ?Societes
    {
        return $this->societes;
    }

    public function setSocietes(?Societes $societes): self
    {
        $this->societes = $societes;

        return $this;
    }

    public function getProfilsUtilisateur(): ?ProfilsUtilisateur
    {
        return $this->profils_utilisateur;
    }

    public function setProfilsUtilisateur(?ProfilsUtilisateur $profils_utilisateur): self
    {
        $this->profils_utilisateur = $profils_utilisateur;

        return $this;
    }

    public function getStatutsUtilisateur(): ?StatutsUtilisateur
    {
        return $this->statuts_utilisateur;
    }

    public function setStatutsUtilisateur(?StatutsUtilisateur $statuts_utilisateur): self
    {
        $this->statuts_utilisateur = $statuts_utilisateur;

        return $this;
    }

    public function getBaseTempsParContrat(): ?BaseTempsParContrat
    {
        return $this->base_temps_par_contrat;
    }

    public function setBaseTempsParContrat(?BaseTempsParContrat $base_temps_par_contrat): self
    {
        $this->base_temps_par_contrat = $base_temps_par_contrat;

        return $this;
    }

    /**
     * @return Collection|JoursAbsence[]
     */
    public function getJoursAbsences(): Collection
    {
        return $this->joursAbsences;
    }

    public function addJoursAbsence(JoursAbsence $joursAbsence): self
    {
        if (!$this->joursAbsences->contains($joursAbsence)) {
            $this->joursAbsences[] = $joursAbsence;
            $joursAbsence->setUser($this);
        }

        return $this;
    }

    public function removeJoursAbsence(JoursAbsence $joursAbsence): self
    {
        if ($this->joursAbsences->contains($joursAbsence)) {
            $this->joursAbsences->removeElement($joursAbsence);
            // set the owning side to null (unless already changed)
            if ($joursAbsence->getUser() === $this) {
                $joursAbsence->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TempsPasse[]
     */
    public function getTempsPasses(): Collection
    {
        return $this->tempsPasses;
    }

    public function addTempsPass(TempsPasse $tempsPass): self
    {
        if (!$this->tempsPasses->contains($tempsPass)) {
            $this->tempsPasses[] = $tempsPass;
            $tempsPass->setUser($this);
        }

        return $this;
    }

    public function removeTempsPass(TempsPasse $tempsPass): self
    {
        if ($this->tempsPasses->contains($tempsPass)) {
            $this->tempsPasses->removeElement($tempsPass);
            // set the owning side to null (unless already changed)
            if ($tempsPass->getUser() === $this) {
                $tempsPass->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProjetParticipant[]
     */
    public function getProjetParticipants(): Collection
    {
        return $this->projetParticipants;
    }

    public function addProjetParticipant(ProjetParticipant $projetParticipant): self
    {
        if (!$this->projetParticipants->contains($projetParticipant)) {
            $this->projetParticipants[] = $projetParticipant;
            $projetParticipant->setUser($this);
        }

        return $this;
    }

    public function removeProjetParticipant(ProjetParticipant $projetParticipant): self
    {
        if ($this->projetParticipants->contains($projetParticipant)) {
            $this->projetParticipants->removeElement($projetParticipant);
            // set the owning side to null (unless already changed)
            if ($projetParticipant->getUser() === $this) {
                $projetParticipant->setUser(null);
            }
        }

        return $this;
    }
}
