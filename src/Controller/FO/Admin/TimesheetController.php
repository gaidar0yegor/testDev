<?php

namespace App\Controller\FO\Admin;

use App\DTO\FilterTimesheet;
use App\Form\FilterTimesheetType;
use App\Repository\UserRepository;
use App\Service\Timesheet\Event\TimesheetEvent;
use App\Service\Timesheet\Export\TimesheetExporter;
use App\Service\Timesheet\TimesheetCalculator;
use DateTime;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TimesheetController extends AbstractController
{
    private Pdf $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @Route(
     *      "/feuille-de-temps/generer",
     *      name="app_fo_admin_timesheet_generate"
     * )
     */
    public function generate(
        Request $request,
        TimesheetCalculator $timesheetCalculator,
        TimesheetExporter $timesheetExporter,
        EventDispatcherInterface $dispatcher,
        UserRepository $userRepository
    ) {
        $users = $userRepository->findBySameSociete($this->getUser());
        $filter = new FilterTimesheet();
        $filter
            ->setUsers($users)
            ->setFrom((new DateTime())->modify('-1 month'))
        ;
        $form = $this->createForm(FilterTimesheetType::class, $filter);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timesheets = $timesheetCalculator->generateMultipleTimesheets($filter);

            $dispatcher->dispatch(new TimesheetEvent($this->getUser()), TimesheetEvent::GENERATED);

            return $timesheetExporter->export($timesheets, $filter->getFormat());
        }

        return $this->render('timesheet/generate.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
