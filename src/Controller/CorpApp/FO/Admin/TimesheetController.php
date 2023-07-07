<?php

namespace App\Controller\CorpApp\FO\Admin;

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
     *      name="corp_app_fo_admin_timesheet_generate"
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
        $societeUsers = $societeUserRepository->findBySameSociete($userContext->getSocieteUser());
        $filter = new FilterTimesheet();
        $filter
            ->setUsers($societeUsers)
            ->setFrom((new DateTime())->modify('-1 month'))
        ;
        $form = $this->createForm(FilterTimesheetType::class, $filter);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timesheets = $timesheetCalculator->generateMultipleTimesheets($filter);

            $dispatcher->dispatch(new TimesheetEvent($userContext->getSocieteUser()), TimesheetEvent::GENERATED);

            return $timesheetExporter->export($timesheets, $filter->getFormat());
        }

        return $this->render('corp_app/timesheet/generate.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
