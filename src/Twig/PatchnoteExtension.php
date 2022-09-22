<?php

namespace App\Twig;

use App\MultiSociete\UserContext;
use App\Repository\PatchnoteRepository;
use Shivas\VersioningBundle\Service\VersionManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PatchnoteExtension extends AbstractExtension
{
    private PatchnoteRepository $patchnoteRepository;
    private UserContext $userContext;
    private VersionManager $versionManager;

    public function __construct(PatchnoteRepository $patchnoteRepository, UserContext $userContext, VersionManager $versionManager)
    {
        $this->patchnoteRepository = $patchnoteRepository;
        $this->userContext = $userContext;
        $this->versionManager = $versionManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getPatchnote', [$this, 'getPatchnote']),
        ];
    }

    public function getPatchnote(?string $rdiApp) : ?array
    {
        if ($rdiApp == null){
            return null;
        }

        if (!$this->userContext->hasUser()){
            return null;
        }

        if ($this->userContext->getUser()->getPatchnoteReaded()){
            return null;
        }

        return $this->patchnoteRepository->findBy(['version' => $this->versionManager->getVersion(), 'isDraft' => false]);
    }
}
