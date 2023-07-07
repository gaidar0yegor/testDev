<?php

namespace App\Controller\CorpApp\FO;

use App\DTO\FilterUserEvenement;
use App\Entity\Evenement;
use App\Entity\Projet;
use App\Form\FilterUserEventType;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Voter\HasProductPrivilegeVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class EvenementController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected UserContext $userContext;

    public function __construct(EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->userContext = $userContext;
    }

    /**
     * @Route("/projet-evenements/{id}", name="corp_app_fo_projet_evenements")
     */
    public function projetEvenements(Projet $projet)
    {
        $this->denyAccessUnlessGranted('view', $projet);

        return $this->render('corp_app/projets/events_calendar.html.twig', [
            'projet' => $projet,
            'userCanEditProjet' => $this->isGranted('edit', $projet),
        ]);
    }

    /**
     * @Route("/mes-evenements", name="corp_app_fo_current_user_events")
     */
    public function currentUserEvents()
    {
        return $this->render('corp_app/projets/user_projets_events.html.twig', [
            'nextEvenementParticipants' => $this->userContext->getSocieteUser()->getNextEvenementParticipants(),
            'oldEvenementParticipants' => $this->userContext->getSocieteUser()->getOldEvenementParticipants(),
        ]);
    }

    /**
     * @Route("/utilisateurs/evenements", name="corp_app_fo_users_events")
     *
     * @IsGranted("SOCIETE_ADMIN")
     */
    public function usersEvents(Request $request, SocieteUserRepository $societeUserRepository)
    {
        if (!$this->userContext->getSocieteUser()->isAdminFo()){
            throw new AccessDeniedException();
        }

        $societeUsers = $societeUserRepository->findBySameSociete($this->userContext->getSocieteUser());

        $filter = new FilterUserEvenement();
        $filter
            ->setUsers($societeUsers)
            ->setEventTypes(Evenement::EVENEMENT_TYPES)
        ;
        $form = $this->createForm(FilterUserEventType::class, $filter);

        $form->handleRequest($request);

        return $this->render('corp_app/projets/users_projets_events.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/equipe/evenements", name="corp_app_fo_equipe_users_events")
     */
    public function equipeUsersEvents(Request $request, SocieteUserRepository $societeUserRepository)
    {
        $this->denyAccessUnlessGranted(HasProductPrivilegeVoter::NAME, ProductPrivileges::SOCIETE_HIERARCHICAL_SUPERIOR);

        if (!$this->userContext->getSocieteUser()->isSuperiorFo()) {
            throw new AccessDeniedException();
        }

        $societeUsers = $societeUserRepository->findTeamMembers($this->userContext->getSocieteUser());

        $filter = new FilterUserEvenement();
        $filter
            ->setUsers($societeUsers)
            ->setEventTypes(Evenement::EVENEMENT_TYPES)
        ;
        $form = $this->createForm(FilterUserEventType::class, $filter, [
            'forTeamMembers' => true
        ]);

        $form->handleRequest($request);

        return $this->render('corp_app/projets/users_projets_events.html.twig', [
            'form' => $form->createView(),
            'forTeamMembers' => true,
        ]);
    }
}
