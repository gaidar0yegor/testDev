<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     *
     * @Assert\NotBlank
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * Clé secrète créée lorsque cet user est invité
     * à rejoindre la société et à finaliser la création de son compte.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $invitationToken;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Si cet utilisateur est activé, et peux se connecter.
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cadre;

    /**
     * @ORM\OneToOne(targetEntity=Licences::class, inversedBy="user", cascade={"persist", "remove"})
     */
    private $licences;

    /**
     * @ORM\ManyToOne(targetEntity=Societe::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     *
     * @Assert\NotBlank(groups={"invitation"})
     */
    private $societe;

    /**
     * @ORM\ManyToOne(targetEntity=ProfilsUtilisateur::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $profils_utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=BaseTempsParContrat::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $base_temps_par_contrat;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="user", orphanRemoval=true)
     */
    private $tempsPasses;

    /**
     * @ORM\OneToMany(targetEntity=ProjetParticipant::class, mappedBy="user", orphanRemoval=true)
     */
    private $projetParticipants;

    /**
     * @ORM\OneToMany(targetEntity=Cra::class, mappedBy="user", orphanRemoval=true)
     */
    private $cras;

    public function __construct()
    {
        $this->enabled = true;
        $this->tempsPasses = new ArrayCollection();
        $this->projetParticipants = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->cras = new ArrayCollection();
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
        return $this->email;
    }

    public function getInvitationToken(): string
    {
        return $this->invitationToken;
    }

    public function setInvitationToken(string $invitationToken): self
    {
        $this->invitationToken = $invitationToken;

        return $this;
    }

    public function removeInvitationToken(): self
    {
        $this->invitationToken = null;

        return $this;
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

    public function getRole(): string
    {
        return $this->getRoles()[0];
    }

    public function setRole(string $role): self
    {
        $this->roles = [$role];

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
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

    public function getFullname(): string
    {
        return $this->getPrenom() . ' ' . $this->getNom();
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

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

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

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(?Societe $societe): self
    {
        $this->societe = $societe;

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

    /**
     * @return Collection|Cra[]
     */
    public function getCras(): Collection
    {
        return $this->cras;
    }

    public function addCra(Cra $cra): self
    {
        if (!$this->cras->contains($cra)) {
            $this->cras[] = $cra;
            $cra->setUser($this);
        }

        return $this;
    }

    public function removeCra(Cra $cra): self
    {
        if ($this->cras->contains($cra)) {
            $this->cras->removeElement($cra);
            // set the owning side to null (unless already changed)
            if ($cra->getUser() === $this) {
                $cra->setUser(null);
            }
        }

        return $this;
    }
}
