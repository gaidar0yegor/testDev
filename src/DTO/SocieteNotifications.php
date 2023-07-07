<?php

namespace App\DTO;

use Cron\CronBundle\Entity\CronJob;

/**
 * Toutes les notifications qu'une société peut personnaliser.
 */
class SocieteNotifications
{
    const CREER_FAITS_MARQUANTS = 'notifie-creer-faits-marquants';
    const DERNIERS_FAITS_MARQUANTS = 'notifie-derniers-faits-marquants';
    const SAISIE_TEMPS = 'notifie-saisie-temps';

    private CronJob $creerFaitsMarquants;

    private CronJob $derniersFaitsMarquants;

    private CronJob $saisieTemps;

    private bool $smsEnabled = false;

    public function getCreerFaitsMarquants(): CronJob
    {
        return $this->creerFaitsMarquants;
    }

    public function setCreerFaitsMarquants(CronJob $cronJob): self
    {
        $this->creerFaitsMarquants = $cronJob;

        return $this;
    }

    public function getDerniersFaitsMarquants(): CronJob
    {
        return $this->derniersFaitsMarquants;
    }

    public function setDerniersFaitsMarquants(CronJob $cronJob): self
    {
        $this->derniersFaitsMarquants = $cronJob;

        return $this;
    }

    public function getSaisieTemps(): CronJob
    {
        return $this->saisieTemps;
    }

    public function setSaisieTemps(CronJob $cronJob): self
    {
        $this->saisieTemps = $cronJob;

        return $this;
    }

    public function getSmsEnabled(): bool
    {
        return $this->smsEnabled;
    }

    public function setSmsEnabled(bool $smsEnabled): self
    {
        $this->smsEnabled = $smsEnabled;

        return $this;
    }

    public function enableAll(): self
    {
        $this->creerFaitsMarquants->setEnabled(true);
        $this->derniersFaitsMarquants->setEnabled(true);
        $this->saisieTemps->setEnabled(true);

        return $this;
    }
}
