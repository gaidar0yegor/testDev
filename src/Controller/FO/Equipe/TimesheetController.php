<?php

namespace App\Controller\FO\Equipe;

use App\DTO\FilterTimesheet;
use App\Form\FilterTimesheetType;
use App\Repository\SocieteUserRepository;
use App\Service\Timesheet\Event\TimesheetEvent;
use App\Service\Timesheet\Export\TimesheetExporter;
use App\Service\Timesheet\TimesheetCalculator;
use App\MultiSociete\UserContext;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TimesheetController extends AbstractController
{
    /**
     * @Route(
     *      "/feuille-de-temps/generer",
     *      name="app_fo_mon_equipe_timesheet_generate"
     * )
     */
    public function generate(
        Request $request,
        TimesheetCalculator $timesheetCalculator,
        TimesheetExporter $timesheetExporter,
        EventDispatcherInterface $dispatcher,
        UserContext $userContext,
        SocieteUserRepository $societeUserRepository
    ) {
        $societeUsers = $societeUserRepository->findTeamMembers($userContext->getSocieteUser());

        $filter = new FilterTimesheet();
        $filter
            ->setUsers($societeUsers)
            ->setFrom((new DateTime())->modify('-1 month'))
        ;
        $form = $this->createForm(FilterTimesheetType::class, $filter,[
            'forTeamMembers' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timesheets = $timesheetCalculator->generateMultipleTimesheets($filter);

            $dispatcher->dispatch(new TimesheetEvent($userContext->getSocieteUser()), TimesheetEvent::GENERATED);

            return $timesheetExporter->export($timesheets, $filter->getFormat());
        }

        return $this->render('timesheet/generate.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
