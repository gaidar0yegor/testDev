<?php

namespace App\Exception;

class UnsupportedTimesheetExportFormatException extends RdiException
{
    public function __construct(string $unsupportedFormat, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'Format "%s" is not supported to export timesheets.',
                $unsupportedFormat
            ),
            $previous
        );
    }
}
