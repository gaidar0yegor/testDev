<?php

namespace App\Service\Timesheet\Export;

use App\DTO\Timesheet;
use App\Exception\UnsupportedTimesheetExportFormatException;
use Symfony\Component\HttpFoundation\Response;

class TimesheetExporter
{
    /**
     * @var FormatInterface[]
     */
    private iterable $exportFormats;

    public function __construct(iterable $exportFormats)
    {
        $this->exportFormats = $exportFormats;
    }

    /**
     * @param Timesheet[] $timesheets
     * @param string $format Format in which we want to export timesheets (html, pdf...)
     *
     * @return Response Symfony response that can be sent to the browser (html or file with the export)
     *
     * @throws UnsupportedTimesheetExportFormatException In case of $format is not supported
     */
    public function export(array $timesheets, string $format): Response
    {
        foreach ($this->exportFormats as $exportFormat) {
            if (!$exportFormat->supports($format)) {
                continue;
            }

            return $exportFormat->createExportResponse($timesheets, $format);
        }

        throw new UnsupportedTimesheetExportFormatException($format);
    }
}
