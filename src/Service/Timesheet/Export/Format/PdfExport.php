<?php

namespace App\Service\Timesheet\Export\Format;

use App\DTO\Timesheet;
use App\Service\Timesheet\Export\FormatInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PdfExport implements FormatInterface
{
    private Pdf $pdf;

    private HtmlExport $htmlExport;

    public function __construct(Pdf $pdf, HtmlExport $htmlExport)
    {
        $this->pdf = $pdf;
        $this->htmlExport = $htmlExport;
    }

    public function supports(string $format): bool
    {
        return 'pdf' === $format;
    }

    /**
     * @param Timesheet[]
     *
     * @return Response The Http Response that should be sent with the export
     */
    public function createExportResponse(array $timesheets, string $format): Response
    {
        $sheetHtml = $this->htmlExport->renderTimesheets($timesheets);

        $options = [
            'margin-top'    => 15,
            'margin-right'  => 15,
            'margin-bottom' => 15,
            'margin-left'   => 15,
            'orientation'   => 'landscape',
        ];

        return new PdfResponse($this->pdf->getOutputFromHtml($sheetHtml, $options), 'feuilles-de-temps.pdf');
    }
}
