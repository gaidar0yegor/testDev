<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\SocieteRepository;
use App\SocieteProduct\Product\StarterProduct;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\DTO\ListCurrencies;

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
    public const DEFAULT_HEURES_PAR_JOURS = 7;

    /**
     * Heure de début du travail définie par défaut
     * lorsque une société est créée.
     *
     * @param float
     */
    public const DEFAULT_WORK_START_TIME = "09:00";

    /**
     * Heure de fin du travail définie par défaut
     * lorsque une société est créée.
     *
     * @param float
     */
    public const DEFAULT_WORK_END_TIME = "17:00";

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
     * This societe has been created from command line, with "app:init-societe-referent".
     *
     * @param string
     */
    public const CREATED_FROM_COMMAND = 'COMMAND';

    /**
     * User fill their timesheet once a month.
     *
     * @param string
     */
    public const GRANULARITY_MONTHLY = 'monthly';

    /**
     * User fill their timesheet once a week.
     *
     * @param string
     */
    public const GRANULARITY_WEEKLY = 'weekly';

    /**
     * User fill their timesheet every day.
     *
     * @param string
     */
    public const GRANULARITY_DAILY = 'daily';

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
     * @ORM\Column(type="string", length=5, nullable=true, options={"default" : self::DEFAULT_WORK_START_TIME})
     *
     * @Assert\Regex(
     *     pattern="/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/",
     *     match= true,
     *     message="Work start time is invalid"
     *     )
     */
    private $workStartTime;

    /**
     * @ORM\Column(type="string", length=5, nullable=true, options={"default" : self::DEFAULT_WORK_END_TIME})
     *
     * @Assert\Regex(
     *     pattern="/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/",
     *     match= true,
     *     message="Work end time is invalid"
     *     )
     */
    private $workEndTime;

    /**
     * Coût moyen d'1 ETP R&D
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $coutEtp;

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

    /**
     * One of self::GRANULARITY_*
     *
     * @ORM\Column(type="string", length=31)
     */
    private $timesheetGranularity;

    /**
     * Limit character of description field of Fait Marquant
     *
     * @ORM\Column(type="integer", options={"default" : 751})
     */
    private $faitMarquantMaxDesc;

    /**
     * Limit character is blocking or not
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $faitMarquantMaxDescIsblocking;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist", "remove"})
     */
    private $logo;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $usedProjectColors = [];

    /**
     * @ORM\Column(type="string", length=10, options={"default" : "#ce352c"})
     */
    private $colorCode;

    /**
     * @ORM\Column(type="string", length=255, options={"default" : "STARTER"})
     */
    private $productKey;

    /**
     * Time to wait before sending another onboarding notification.
     *
     * @ORM\Column(type="string", length=255, options={"default" : "2 weeks"})
     */
    private $onboardingNotificationEvery;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default" : ListCurrencies::DEFAULT_CURRENCY})
     */
    private $currency;

    /**
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $enabled;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $onStandBy;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $disabledAt;

    public function __construct()
    {
        $this->enabled = true;
        $this->onStandBy = false;
        $this->uuid = Uuid::uuid4();
        $this->societeUsers = new ArrayCollection();
        $this->heuresParJours = self::DEFAULT_HEURES_PAR_JOURS;
        $this->workStartTime = self::DEFAULT_WORK_START_TIME;
        $this->workEndTime = self::DEFAULT_WORK_END_TIME;
        $this->smsEnabled = true;
        $this->slackAccessTokens = new ArrayCollection();
        $this->projets = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->timesheetGranularity = self::GRANULARITY_MONTHLY;
        $this->faitMarquantMaxDesc = 751;
        $this->faitMarquantMaxDescIsblocking = true;
        $this->colorCode = '#ce352c';
        $this->productKey = StarterProduct::PRODUCT_KEY;
        $this->onboardingNotificationEvery = '2 weeks';
        $this->currency = ListCurrencies::DEFAULT_CURRENCY;
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

    /**
     * @return Collection|SocieteUser[]
     */
    public function getEnableSocieteUsers(): Collection
    {
        return $this->societeUsers->filter(function (SocieteUser $societeUser) {
            return $societeUser->getEnabled();
        });
    }

    /**
     * @return Collection|SocieteUser[]
     */
    public function getDisableSocieteUsers(): Collection
    {
        return $this->societeUsers->filter(function (SocieteUser $societeUser) {
            return !$societeUser->getEnabled();
        });
    }

    /**
     * @return Collection|SocieteUser[]
     */
    public function getActiveSocieteUsers(): Collection
    {
        return $this->societeUsers->filter(function (SocieteUser $societeUser) {
            return $societeUser->hasUser() && $societeUser->getEnabled();
        });
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

    public function getTimesheetGranularity(): ?string
    {
        return $this->timesheetGranularity;
    }

    public function setTimesheetGranularity(string $timesheetGranularity): self
    {
        $this->timesheetGranularity = $timesheetGranularity;

        return $this;
    }

    public function getFaitMarquantMaxDesc(): ?int
    {
        return $this->faitMarquantMaxDesc;
    }

    public function setFaitMarquantMaxDesc(int $faitMarquantMaxDesc): self
    {
        $this->faitMarquantMaxDesc = $faitMarquantMaxDesc;

        return $this;
    }

    public function getFaitMarquantMaxDescIsblocking(): ?bool
    {
        return $this->faitMarquantMaxDescIsblocking;
    }

    public function setFaitMarquantMaxDescIsblocking(bool $faitMarquantMaxDescIsblocking): self
    {
        $this->faitMarquantMaxDescIsblocking = $faitMarquantMaxDescIsblocking;

        return $this;
    }

    public function getLogo(): ?Fichier
    {
        return $this->logo;
    }

    public function setLogo(?Fichier $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getUsedProjectColors(): ?array
    {
        return $this->usedProjectColors;
    }

    public function setUsedProjectColors(?array $usedProjectColors): self
    {
        $this->usedProjectColors = $usedProjectColors;

        return $this;
    }

    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    public function setColorCode(string $colorCode): self
    {
        $this->colorCode = $colorCode;

        return $this;
    }

    public function getProductKey(): ?string
    {
        return $this->productKey;
    }

    public function setProductKey(string $productKey): self
    {
        $this->productKey = $productKey;

        return $this;
    }

    public function getOnboardingNotificationEvery(): ?string
    {
        return $this->onboardingNotificationEvery;
    }

    public function setOnboardingNotificationEvery(string $onboardingNotificationEvery): self
    {
        $this->onboardingNotificationEvery = $onboardingNotificationEvery;

        return $this;
    }

    public function getCoutEtp(): ?string
    {
        return $this->coutEtp;
    }

    public function setCoutEtp(?string $coutEtp): self
    {
        $this->coutEtp = $coutEtp;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getOnStandBy(): ?bool
    {
        return $this->onStandBy;
    }

    public function setOnStandBy(bool $onStandBy): self
    {
        $this->onStandBy = $onStandBy;

        return $this;
    }

    public function getDisabledAt(): ?\DateTimeInterface
    {
        return $this->disabledAt;
    }

    public function setDisabledAt(?\DateTimeInterface $disabledAt): self
    {
        $this->disabledAt = $disabledAt;

        return $this;
    }

    public function getWorkStartTime(): ?string
    {
        return $this->workStartTime;
    }

    public function setWorkStartTime(?string $workStartTime): self
    {
        $this->workStartTime = $workStartTime;

        return $this;
    }

    public function getWorkEndTime(): ?string
    {
        return $this->workEndTime;
    }

    public function setWorkEndTime(?string $workEndTime): self
    {
        $this->workEndTime = $workEndTime;

        return $this;
    }
}
