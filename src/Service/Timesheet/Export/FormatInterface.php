<?php

namespace App\Service\Timesheet\Export;

use App\DTO\Timesheet;
use Symfony\Component\HttpFoundation\Response;

interface FormatInterface
{
    public function supports(string $format): bool;

    /**
     * @param Timesheet[] $timesheets
     *
     * @return Response The Http Response that should be sent with the export
     */
    public function createExportResponse(array $timesheets): Response;
}
