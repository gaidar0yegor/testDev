<?php

namespace App\Service\Timesheet\Export\Format;

use App\DTO\Timesheet;
use App\Service\Timesheet\Export\FormatInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class HtmlExport implements FormatInterface
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function supports(string $format): bool
    {
        return 'html' === $format;
    }

    /**
     * @param Timesheet[]
     *
     * @return Response The Http Response that should be sent with the export
     */
    public function createExportResponse(array $timesheets, string $format): Response
    {
        return new Response($this->renderTimesheets($timesheets));
    }

    public function renderTimesheets(array $timesheets): string
    {
        return $this->twig->render('corp_app/timesheet/pdf/pdf.html.twig', [
            'timesheets' => $timesheets,
        ]);
    }
}
