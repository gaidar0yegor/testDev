<?php

namespace App\Onboarding\Step;

use App\Entity\SocieteUser;
use App\Onboarding\OnboardingStepInterface;
use App\Repository\ProjetRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddProjetStep implements OnboardingStepInterface
{
    private ProjetRepository $projetRepository;

    public function __construct(ProjetRepository $projetRepository)
    {
        $this->projetRepository = $projetRepository;
    }

    public function getText(): string
    {
        return 'Ajoutez vos projets';
    }

    public function getLink(UrlGeneratorInterface $urlGenerator, SocieteUser $societeUser): ?string
    {
        return $urlGenerator->generate(
            'corp_app_fo_projet_creation',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function isImportant(): bool
    {
        return true;
    }

    public function isCompleted(SocieteUser $societeUser): bool
    {
        $projets = $this
            ->projetRepository
            ->findAllProjectsPerSociete($societeUser->getSociete())
        ;

        return count($projets) > 0;
    }

    public static function getPriority(): int
    {
        return 50;
    }
}
