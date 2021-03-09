<?php

namespace App\License\DTO;

use App\Entity\Societe;
use App\HasSocieteInterface;
use App\License\Quota\ActiveProjetQuota;
use App\License\Quota\ContributeurQuota;
use DateTime;

class License implements HasSocieteInterface
{
    private string $name;

    private ?string $description;

    private Societe $societe;

    private DateTime $expirationDate;

    private array $quotas;

    public static function createFreeLicenseFor(Societe $societe): License
    {
        $license = new License();

        $license
            ->setName('Offre starter')
            ->setSociete($societe)
            ->setExpirationDate((new DateTime())->modify('+1 year'))
            ->setQuotas([
                ActiveProjetQuota::NAME => 2,
                ContributeurQuota::NAME => 3,
            ])
        ;

        return $license;
    }

    /**
     * Create unlimited license for testing.
     */
    public static function createUnlimitedLicense(Societe $societe, $expirationDate = null): License
    {
        $license = new License();

        if (null === $expirationDate) {
            $expirationDate = (new DateTime())->modify('+1 day');
        }

        $license
            ->setName('Unlimited license for dev or test')
            ->setSociete($societe)
            ->setExpirationDate($expirationDate)
            ->setQuotas([
                ActiveProjetQuota::NAME => 1E6,
                ContributeurQuota::NAME => 1E6,
            ])
        ;

        return $license;
    }

    public function __construct()
    {
        $this->description = null;
        $this->quotas = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(Societe $societe)
    {
        $this->societe = $societe;

        return $this;
    }

    public function getExpirationDate(): DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getQuotas(): array
    {
        return $this->quotas;
    }

    public function setQuotas(array $quotas): self
    {
        $this->quotas = $quotas;

        return $this;
    }
}
