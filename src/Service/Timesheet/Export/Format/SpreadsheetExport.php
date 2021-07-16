<?php

namespace App\Service\Timesheet\Export\Format;

use App\DTO\Timesheet;
use App\File\FileResponseFactory;
use App\Service\Timesheet\Export\FormatInterface;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Column;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class SpreadsheetExport implements FormatInterface
{
    private string $tmpDir;

    private FileResponseFactory $fileResponseFactory;

    private TranslatorInterface $translator;

    public function __construct(
        string $tmpDir,
        FileResponseFactory $fileResponseFactory,
        TranslatorInterface $translator
    ) {
        $this->tmpDir = $tmpDir;
        $this->fileResponseFactory = $fileResponseFactory;
        $this->translator = $translator;

        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }
    }

    public function supports(string $format): bool
    {
        return in_array($format, [
            'ods',
            'xlsx',
            'xls',
        ]);
    }

    public function createExportResponse(array $timesheets, string $format): Response
    {
        $spreadsheet = $this->createSpreadsheet($timesheets);
        $tmpFilename = $this->tmpDir.'/'.md5(uniqid()).'.ods';

        IOFactory::createWriter($spreadsheet, ucfirst($format))->save($tmpFilename);

        $mimetypes = [
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
        ];

        $response = $this->fileResponseFactory->createFileResponse(
            fopen($tmpFilename, 'r'),
            "feuilles-de-temps.$format",
            $mimetypes[$format]
        );

        unlink($tmpFilename);

        return $response;
    }

    /**
     * @param Timesheet[] $timesheets
     *
     * @return Spreadsheet
     */
    private function createSpreadsheet(array $timesheets): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->removeSheetByIndex($spreadsheet->getActiveSheetIndex());

        foreach ($timesheets as $timesheet) {
            $worksheet = new Worksheet(
                $spreadsheet,
                sprintf(
                    '%s - %s',
                    $timesheet->getCra()->getMois()->format('Y-m'),
                    substr($timesheet->getCra()->getSocieteUser()->getUser()->getFullname(), 0, 20)
                )
            );

            $spreadsheet->addSheet($worksheet);

            $this->fillWorksheet($worksheet, $timesheet);
        }

        return $spreadsheet;
    }

    private function fillWorksheet(Worksheet $worksheet, Timesheet $timesheet): void
    {
        // Set columns width
        $worksheet->getDefaultColumnDimension()->setWidth(5);
        $worksheet->getColumnDimension('A')->setWidth(12);
        $worksheet->getColumnDimension('B')->setWidth(14);
        $worksheet->getColumnDimension('C')->setWidth(8);
        $worksheet->getColumnDimension('D')->setWidth(8);

        $colorPresence = '28A745';
        $colorDemiJournee = '17A2B8';
        $colorAbsence = '6C757D';

        // Add user info
        $societeUser = $timesheet->getCra()->getSocieteUser();
        $user = $societeUser->getUser();

        $worksheet->fromArray([
            ['Nom', $user->getNom()],
            ['Prénom', $user->getPrenom()],
            ['Email', $user->getEmail()],
            ['Société', $societeUser->getSociete()->getRaisonSociale()],
            ['Heures/jour', $timesheet->getHeuresParJours().' h'],
            ['Mois', $timesheet->getCra()->getMois()->format('F Y')],
        ]);

        // Add header
        $monthCraHeader = [
            'Projet',
            'Rôle',
            'Temps'.PHP_EOL.'passé',
            'Total'.PHP_EOL.'heures',
        ];

        $month = $timesheet->getCra()->getMois();
        $daysInMonth = intval($month->format('t'));
        $projetsCount = count($timesheet->getTimesheetProjets());

        for ($i = 0; $i < $daysInMonth; ++$i) {
            $day = (new DateTime($month->format('Y-m-d')))->modify("+$i days");
            $monthCraHeader[] = $day->format('D'.PHP_EOL.'j');

            $presence = $timesheet->getCra()->getJours()[$i];

            if ($presence >= 1) {
                $fillColor = $colorPresence;
            } elseif ($presence > 0) {
                $fillColor = $colorDemiJournee;
            } else {
                $fillColor = $colorAbsence;
            }

            $worksheet
                ->getStyleByColumnAndRow($i + 5, 8, $i + 5, 8 + $projetsCount)
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($fillColor)
            ;
        }

        $monthCra = [$monthCraHeader];

        // Add Cra
        foreach ($timesheet->getTimesheetProjets() as $timesheetProjet) {
            $projetLine = [
                $timesheetProjet->getProjetParticipant()->getProjet()->getAcronyme(),
                $this->translator->trans($timesheetProjet->getProjetParticipant()->getRole()),
                null === $timesheetProjet->getTempsPasse() ? '0 %' : $timesheetProjet->getTempsPasse()->getPourcentage().' %',
                $timesheetProjet->getTotalWorkedHours().' h',
            ];

            if (null !== $timesheetProjet->getWorkedHours()) {
                array_push($projetLine, ...$timesheetProjet->getWorkedHours());
            }

            $monthCra[] = $projetLine;
        }

        // Add Total
        $monthCra[] = [
            null,
            'Total du mois',
            $timesheet->getTotalPourcentage().' %',
            $timesheet->getTotalWorkedHours().' h',
        ];

        $worksheet->fromArray($monthCra, null, 'A8');

        // Add legend
        $legend = [
            ['Jour de présence', null, $timesheet->getSumJourPresence()],
            ['Demi journée', null, $timesheet->getSumJourDemiJournees()],
            ['Jour d\'absence'],
        ];
        $worksheet->fromArray($legend, null, 'C'.(8 + $projetsCount + 4));
        $worksheet
            ->getStyleByColumnAndRow(3, 8 + $projetsCount + 4, 3 + 2, 8 + $projetsCount + 4)
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($colorPresence)
        ;
        $worksheet
            ->getStyleByColumnAndRow(3, 8 + $projetsCount + 5, 3 + 2, 8 + $projetsCount + 5)
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($colorDemiJournee)
        ;
        $worksheet
            ->getStyleByColumnAndRow(3, 8 + $projetsCount + 6, 3 + 2, 8 + $projetsCount + 6)
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($colorAbsence)
        ;

        // Set more height for header
        $worksheet->getRowDimension(8)->setRowHeight(30);

        // Set header bold
        $worksheet
            ->getStyleByColumnAndRow(1, 8, 5 + $daysInMonth, 8)
            ->getFont()->setBold(true)
        ;

        // Add table borders
        $worksheet
            ->getStyleByColumnAndRow(1, 8, 1 + $daysInMonth + 3, 8 + $projetsCount)
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rbg' => '000000'],
                    ],
                ],
            ])
        ;

        // Center days values
        $worksheet
            ->getStyleByColumnAndRow(5, 8, 5 + $daysInMonth, 8 + $projetsCount)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ;

        // Set more height for Total
        $worksheet->getRowDimension(8 + $projetsCount + 1)->setRowHeight(30);
    }
}
