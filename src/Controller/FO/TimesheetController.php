<?php

namespace App\Controller\FO;

use App\Entity\Cra;
use App\Repository\CraRepository;
use App\Service\TimesheetCalculator;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_FO_ADMIN")
 */
class TimesheetController extends AbstractController
{
    /**
     * @Route(
     *      "/feuille-de-temps/generer",
     *      name="timesheet_generate"
     * )
     */
    public function generate()
    {
        return $this->render('timesheet/generate.html.twig');
    }

    /**
     * @Route(
     *      "/feuille-de-temps-{id}.{format}",
     *      requirements={"format"="(html|pdf)"},
     *      name="timesheet_sheet"
     * )
     */
    public function sheet(string $format, Cra $cra, Pdf $pdf, TimesheetCalculator $timesheetCalculator, CraRepository $cr)
    {
        $sheetHtml = $this->renderView('timesheet/pdf/pdf.html.twig', [
            //'timesheet' => $timesheetCalculator->generateTimesheet($cra),
            'timesheets' => array_map(function ($cra) use ($timesheetCalculator) {
                return $timesheetCalculator->generateTimesheet($cra);
            }, $cr->findAll()),
        ]);

        if ('html' === $format) {
            return new Response($sheetHtml);
        }

        $options = [
            'margin-top'    => 15,
            'margin-right'  => 15,
            'margin-bottom' => 15,
            'margin-left'   => 15,
            'background'    => true,
        ];

        return new PdfResponse($pdf->getOutputFromHtml($sheetHtml, $options), 'timesheet.pdf');
    }
}
