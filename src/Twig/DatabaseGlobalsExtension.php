<?php

namespace App\Twig;

use App\Entity\Projet;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class DatabaseGlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    protected EntityManagerInterface $em;
    protected UserContext $userContext;

    public function __construct(EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->userContext = $userContext;
    }

    public function getGlobals():array
    {
        $sidebarProjets = $this->userContext->hasSocieteUser()
            ? $this->em->getRepository(Projet::class)->findAllForUserInYear($this->userContext->getSocieteUser())
            : [];

        return [
            'sidebarProjets' => $sidebarProjets
        ];
    }
}
