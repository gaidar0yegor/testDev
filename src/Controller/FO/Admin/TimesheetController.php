<?php

namespace App\Controller\FO\Admin;

use App\DTO\FilterTimesheet;
use App\Entity\User;
use App\Exception\MonthOutOfRangeException;
use App\Exception\TimesheetException;
use App\Form\FilterTimesheetType;
use App\Repository\UserRepository;
use App\Service\DateMonthService;
use App\Service\Timesheet\TimesheetCalculator;
use DateTime;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

            $sheetHtml = $this->renderView('timesheet/pdf/pdf.html.twig', [
                'timesheets' => $timesheets,
            ]);

            if ('html' === $filter->getFormat()) {
                return new Response($sheetHtml);
            }

            return $this->createPdfResponse($sheetHtml);
        }

        return $this->render('timesheet/generate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Génère une unique feuille de temps.
     *
     * @Route(
     *      "/feuille-de-temps-{year}-{month}-{userId}.{format}",
     *      requirements={"format"="(html|pdf)"},
     *      name="app_fo_admin_timesheet_sheet"
     * )
     *
     * @ParamConverter("user", options={"id" = "userId"})
     */
    public function sheet(
        string $year,
        string $month,
        User $user,
        string $format,
        DateMonthService $dateMonthService,
        TimesheetCalculator $timesheetCalculator
    ) {
        try {
            $date = $dateMonthService->getMonthFromYearAndMonth($year, $month);
            $timesheet = $timesheetCalculator->generateTimesheet($user, $date);
        } catch (MonthOutOfRangeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        } catch (TimesheetException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $sheetHtml = $this->renderView('timesheet/pdf/pdf.html.twig', [
            'timesheet' => $timesheet,
        ]);

        if ('html' === $format) {
            return new Response($sheetHtml);
        }

        return $this->createPdfResponse($sheetHtml);
    }

    private function createPdfResponse(string $htmlContent, string $filename = 'feuille-de-temps.pdf'): PdfResponse
    {
        $options = [
            'margin-top'    => 15,
            'margin-right'  => 15,
            'margin-bottom' => 15,
            'margin-left'   => 15,
            'orientation'   => 'landscape',
        ];

        return new PdfResponse($this->pdf->getOutputFromHtml($htmlContent, $options), $filename);
    }
}
