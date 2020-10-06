<?php

namespace App\Entity;

use App\Repository\ProfilsUtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfilsUtilisateurRepository::class)
 */
class ProfilsUtilisateur
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $back_office;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Users::class, mappedBy="profils_utilisateur")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBackOffice(): ?bool
    {
        return $this->back_office;
    }

    public function setBackOffice(bool $back_office): self
    {
        $this->back_office = $back_office;

        return $this;
    }

    public function getRoleBackOffice(): ?string
    {
        return $this->role_back_office;
    }

    public function setRoleBackOffice(string $role_back_office): self
    {
        $this->role_back_office = $role_back_office;

        return $this;
    }

    public function getRoleFrontOffice(): ?string
    {
        return $this->role_front_office;
    }

    public function setRoleFrontOffice(string $role_front_office): self
    {
        $this->role_front_office = $role_front_office;

        return $this;
    }

    /**
     * @return Collection|Users[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(Users $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setProfilsUtilisateur($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getProfilsUtilisateur() === $this) {
                $user->setProfilsUtilisateur(null);
            }
        }

        return $this;
    }
}
