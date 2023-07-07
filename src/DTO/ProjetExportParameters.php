<?php

namespace App\DTO;
use App\Entity\Projet;
use DateTime;

class ProjetExportParameters
{
    const PRESENTATION = 'PRESENTATION';
    const FAITS_MARQUANTS = 'FAITS_MARQUANTS';
    const LISTE_FICHIERS = 'LISTE_FICHIERS';
    const ACTIVITES = 'ACTIVITES';
    const PARTICIPANTS = 'PARTICIPANTS';
    const STATISTIQUES = 'STATISTIQUES';

    private ?DateTime $dateDebut;

    private ?DateTime $dateFin;

    /**
     * "pdf" or "html"
     */
    private string $format;

    private array $exportOptions;

    private Projet $projet;

    public function __construct(Projet $projet)
    {
        $this->projet = $projet;
        $this->dateDebut = $this->projet->getDateDebut();
        $this->dateFin = $this->projet->getDateFin();
        $this->format = 'pdf';
        $this->exportOptions = [
            $this::PRESENTATION,
            $this::FAITS_MARQUANTS,
            $this::STATISTIQUES,
            $this::PARTICIPANTS,
            $this::ACTIVITES,
            $this::LISTE_FICHIERS,
        ];
    }

    public function getProjet(): Projet
    {
        return $this->projet;
    }

    public function getdateDebut(): ?DateTime
    {
        return $this->dateDebut;
    }

    public function setdateDebut(?DateTime $from): self
    {
        $this->dateDebut = $from;

        return $this;
    }

    public function getdateFin(): ?DateTime
    {
        return $this->dateFin;
    }

    public function setdateFin(?DateTime $to): self
    {
        $this->dateFin = $to;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getExportOptions(): array
    {
        return $this->exportOptions;
    }

    public function setExportOptions(array $exportOptions): self
    {
        $this->exportOptions = $exportOptions;

        return $this;
    }
}
