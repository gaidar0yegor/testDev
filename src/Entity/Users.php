<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users implements UserInterface
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
     * @ORM\Column(type="string")
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
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deleted_at;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $deleted_by;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cadre;

    /**
     * @ORM\OneToOne(targetEntity=Licences::class, inversedBy="users", cascade={"persist", "remove"})
     */
    private $licences;

    /**
     * @ORM\ManyToOne(targetEntity=Societes::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $societes;

    /**
     * @ORM\ManyToOne(targetEntity=ProfilsUtilisateur::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $profils_utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=StatutsUtilisateur::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $statuts_utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=BaseTempsParContrat::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $base_temps_par_contrat;

    /**
     * @ORM\OneToMany(targetEntity=JoursAbsence::class, mappedBy="users", orphanRemoval=true)
     */
    private $joursAbsences;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="users", orphanRemoval=true)
     */
    private $tempsPasses;

    /**
     * @ORM\OneToMany(targetEntity=ParticipantsProjet::class, mappedBy="users", orphanRemoval=true)
     */
    private $participantsProjets;

    public function __construct()
    {
        $this->joursAbsences = new ArrayCollection();
        $this->tempsPasses = new ArrayCollection();
        $this->participantsProjets = new ArrayCollection();
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
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeInterface $deleted_at): self
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    public function getDeletedBy(): ?string
    {
        return $this->deleted_by;
    }

    public function setDeletedBy(?string $deleted_by): self
    {
        $this->deleted_by = $deleted_by;

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
            $joursAbsence->setUsers($this);
        }

        return $this;
    }

    public function removeJoursAbsence(JoursAbsence $joursAbsence): self
    {
        if ($this->joursAbsences->contains($joursAbsence)) {
            $this->joursAbsences->removeElement($joursAbsence);
            // set the owning side to null (unless already changed)
            if ($joursAbsence->getUsers() === $this) {
                $joursAbsence->setUsers(null);
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
            $tempsPass->setUsers($this);
        }

        return $this;
    }

    public function removeTempsPass(TempsPasse $tempsPass): self
    {
        if ($this->tempsPasses->contains($tempsPass)) {
            $this->tempsPasses->removeElement($tempsPass);
            // set the owning side to null (unless already changed)
            if ($tempsPass->getUsers() === $this) {
                $tempsPass->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ParticipantsProjet[]
     */
    public function getParticipantsProjets(): Collection
    {
        return $this->participantsProjets;
    }

    public function addParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if (!$this->participantsProjets->contains($participantsProjet)) {
            $this->participantsProjets[] = $participantsProjet;
            $participantsProjet->setUsers($this);
        }

        return $this;
    }

    public function removeParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if ($this->participantsProjets->contains($participantsProjet)) {
            $this->participantsProjets->removeElement($participantsProjet);
            // set the owning side to null (unless already changed)
            if ($participantsProjet->getUsers() === $this) {
                $participantsProjet->setUsers(null);
            }
        }

        return $this;
    }
}
