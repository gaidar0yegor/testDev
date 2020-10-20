<?php

namespace App\Service;

use App\DTO\ListeTempsPasses;
use App\Entity\Projet;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Repository\ProjetRepository;
use App\Repository\TempsPasseRepository;

/**
 * Service pour gerer les temps passés.
 */
class TempsPasseService
{
    private $projetRepository;

    private $tempsPasseRepository;

    private $dateMonthService;

    public function __construct(ProjetRepository $projetRepository, TempsPasseRepository $tempsPasseRepository, DateMonthService $dateMonthService)
    {
        $this->projetRepository = $projetRepository;
        $this->tempsPasseRepository = $tempsPasseRepository;
        $this->dateMonthService = $dateMonthService;
    }

    /**
     * Initialize la liste de temps passés pour un utilisateur lors d'un mois.
     * Récupère les projets de l'utilisateur, et prérempli ses pourcentages déjà saisis.
     */
    public function loadTempsPasses(User $user, \DateTime $mois): ListeTempsPasses
    {
        $this->dateMonthService->normalize($mois);

        $userProjets = $this->projetRepository->findAllForUser($user);
        $tempsPasses = $this->tempsPasseRepository->findAllForUserAndMonth($user, $mois);

        foreach ($userProjets as $userProjet) {
            if ($this->tempsPassesContainsProjet($tempsPasses, $userProjet)) {
                continue;
            }

            $tempsPasse = new TempsPasse();

            $tempsPasse
                ->setUser($user)
                ->setMois($mois)
                ->setProjet($userProjet)
                ->setPourcentage(0)
            ;

            $tempsPasses[] = $tempsPasse;
        }

        return new ListeTempsPasses($tempsPasses);
    }

    /**
     * @param TempsPasse[] $tempsPasses Liste de temps passés à verifier si un est lié au $projet.
     * @param Projet $projet
     *
     * @return bool Si Un des temps passé correspond au projet.
     */
    private function tempsPassesContainsProjet(array $tempsPasses, Projet $projet): bool
    {
        foreach ($tempsPasses as $tempsPasse) {
            if ($tempsPasse->getProjet() === $projet) {
                return true;
            }
        }

        return false;
    }
}
