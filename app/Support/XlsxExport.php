<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxExport
{
    /**
     * @param  array<int, string>  $headings     judul kolom
     * @param  iterable<array>     $rows         tiap baris berupa array nilai (Carbon = tanggal)
     * @param  array<int, int>     $widths       lebar tiap kolom, urut dari kolom pertama
     * @param  array<int, int>     $dateColumns  nomor kolom bertanggal (mulai dari 1)
     */
    public static function download(
        string $filename,
        string $sheetTitle,
        array $headings,
        iterable $rows,
        array $widths = [],
        array $dateColumns = [],
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        foreach ($headings as $i => $heading) {
            $sheet->setCellValueExplicit([$i + 1, 1], $heading, DataType::TYPE_STRING);
        }

        $rowNumber = 2;

        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $value) {
                $cell = [$i + 1, $rowNumber];

                if ($value instanceof \DateTimeInterface) {
                    $sheet->setCellValue($cell, Date::PHPToExcel($value));
                } elseif (is_int($value) || is_float($value)) {
                    $sheet->setCellValue($cell, $value);
                } elseif ($value !== null && $value !== '') {
                    // Selalu sebagai teks, sehingga "=1+1" atau "00123" tidak diubah Excel
                    $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
                }
            }

            $rowNumber++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headings));
        $lastRow = $rowNumber - 1;

        // Header
        $header = $sheet->getStyle("A1:{$lastColumn}1");
        $header->getFont()->setBold(true);
        $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E7FF');
        $header->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Lebar kolom
        foreach ($widths as $i => $width) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($width);
        }

        // Format kolom tanggal
        if ($lastRow >= 2) {
            foreach ($dateColumns as $column) {
                $letter = Coordinate::stringFromColumnIndex($column);
                $range = $sheet->getStyle("{$letter}2:{$letter}{$lastRow}");
                $range->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $range->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
