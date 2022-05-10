<?php

namespace App\Controller\FO;

use App\Entity\Projet;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/projet/{projetId}/events")
 */
class ProjetEventsCalendarController extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="app_fo_projet_events", requirements={"projetId"="\d+"})
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function show(Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('projets/events_calendar.html.twig', [
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet),
        ]);
    }
}
