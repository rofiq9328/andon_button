<?php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export data audit andon ke Excel, dengan susunan yang sama seperti
 * file Excel asli: sheet bulan (NOVEMBER) dan sheet SUMMARY.
 */
class AuditExportController extends BaseController
{
    private const COMPANY   = 'PT. KKANINDO MAKMUR JAYA 2';
    private const AREA      = 'ALL FACTORY';
    private const FIRST_ROW = 10;   // baris data pertama di sheet bulan
    private const SUM_FIRST = 31;   // baris data pertama di tabel PERLINE

    private const MONTHS = [
        'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
        'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER',
    ];

    /** Huruf kolom => field di database, urut sesuai file Excel. */
    private const COLUMNS = [
        'C' => 'tersedia',
        'D' => 'kondisi_remote',
        'E' => 'visual_nomor',
        'F' => 'material_lampu',
        'G' => 'material_layar',
        'H' => 'mekanik_lampu',
        'I' => 'mekanik_layar',
        'J' => 'qc_lampu',
        'K' => 'qc_layar',
        'L' => 'spv_lampu',
        'M' => 'spv_layar',
        'N' => 'perbaikan_rusak',
        'O' => 'ganti_baterai',
        'P' => 'catatan',
    ];

    public function excel()
    {
        if (!class_exists(Spreadsheet::class)) {
            return redirect()->to('audit-andon')
                ->with('error', 'PhpSpreadsheet belum terpasang di server. Jalankan composer install.');
        }

        // Export bisa memakan waktu dan memori lebih dari default XAMPP
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $periode = (string) $this->request->getGet('periode');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
            $periode = date('Y-m');
        }

        [$year, $month] = explode('-', $periode);
        $monthName = self::MONTHS[(int) $month - 1];

        try {
            $units = db_connect()->table('audit_andon_units')
                ->orderBy('line', 'ASC')
                ->orderBy('line_no', 'ASC')
                ->get()
                ->getResultArray();

            $details = [];
            foreach (db_connect()->table('audit_andon_detail')
                         ->where('periode', $periode)
                         ->get()
                         ->getResultArray() as $d) {
                $details[$d['line'] . '-' . $d['line_no']] = $d;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($monthName);

            $this->writeHeader($sheet, $year);
            $last = $this->writeUnits($sheet, $units, $details);

            $summary = $spreadsheet->createSheet();
            $summary->setTitle('SUMMARY');
            $lines = array_values(array_unique(array_map(static fn ($u) => (int) $u['line'], $units)));
            $this->writeSummary($summary, $year, $monthName, $lines, $last);

            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            log_message('error', 'Export audit gagal: ' . $e->getMessage());

            return redirect()->to('audit-andon?periode=' . $periode)
                ->with('error', 'Export gagal: ' . $e->getMessage());
        }

        $filename = 'Audit Andon ' . $monthName . ' ' . $year . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }

    /**
     * Judul dan header tabel (baris 1-8), sama dengan sheet asli.
     */
    private function writeHeader(Worksheet $sheet, string $year): void
    {
        $sheet->setCellValue('H1', 'DATA REKAN ANDON ' . $year);
        $sheet->setCellValue('H2', self::COMPANY);
        $sheet->setCellValue('H3', self::AREA);
        $sheet->setCellValue('N4', date('d/m/Y'));
        $sheet->getStyle('H1:H3')->getFont()->setBold(true);

        $sheet->setCellValue('A5', 'CEK REMOTE ANDON');
        $sheet->getStyle('A5')->getFont()->setBold(true);

        $sheet->setCellValue('A6', 'LINE');
        $sheet->setCellValue('B6', 'LINE NO');
        $sheet->setCellValue('C6', 'TERSEDIA');
        $sheet->setCellValue('D6', 'KONDISI REMOTE (ON/OFF)');
        $sheet->setCellValue('E6', 'VISUAL NOMOR (Depan/Belakang)');
        $sheet->setCellValue('F6', 'TOMBOL');
        $sheet->setCellValue('F7', 'MATERIAL');
        $sheet->setCellValue('H7', 'MEKANIK');
        $sheet->setCellValue('J7', 'QC/QA');
        $sheet->setCellValue('L7', 'SPV');

        $sub = [
            'F8' => 'LAMPU', 'G8' => 'LAYAR',
            'H8' => 'LAMPU', 'I8' => 'LAYAR',
            'J8' => 'LAMPU', 'K8' => 'LAYAR',
            'L8' => 'LAMPU', 'M8' => 'LAYAR',
        ];
        foreach ($sub as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->setCellValue('N6', 'PERBAIKAN');
        $sheet->setCellValue('N7', 'RUSAK');
        $sheet->setCellValue('O7', 'GANTI BATERAI');
        $sheet->setCellValue('P6', 'CATATAN');

        foreach (['A6:A8', 'B6:B8', 'C6:C8', 'D6:D8', 'E6:E8', 'F6:M6', 'F7:G7', 'H7:I7', 'J7:K7', 'L7:M7', 'N6:O6', 'N7:N8', 'O7:O8', 'P6:P8'] as $range) {
            $sheet->mergeCells($range);
        }

        $sheet->getStyle('A6:P8')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '8FBF9F']]],
        ]);

        $widths = [
            'A' => 10, 'B' => 9,  'C' => 14, 'D' => 18, 'E' => 18,
            'F' => 11, 'G' => 11, 'H' => 11, 'I' => 11, 'J' => 11,
            'K' => 11, 'L' => 11, 'M' => 11, 'N' => 14, 'O' => 16, 'P' => 34,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $sheet->getRowDimension(6)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(18);
        $sheet->getRowDimension(8)->setRowHeight(18);
    }

    /**
     * Isi baris unit (mulai baris 10) sekaligus, bukan per sel.
     * Mengembalikan nomor baris terakhir.
     */
    private function writeUnits(Worksheet $sheet, array $units, array $details): int
    {
        $fields = array_values(self::COLUMNS);
        $rows   = [];

        foreach ($units as $u) {
            $d   = $details[$u['line'] . '-' . $u['line_no']] ?? [];
            $row = [(int) $u['line'], (int) $u['line_no']];

            foreach ($fields as $field) {
                $row[] = $d[$field] ?? null;
            }

            $rows[] = $row;
        }

        // Nilai null dilewati, jadi sel kosong tetap kosong
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A' . self::FIRST_ROW, false);
        }

        $last = self::FIRST_ROW + count($rows) - 1;

        if ($last >= self::FIRST_ROW) {
            $sheet->getStyle('A' . self::FIRST_ROW . ':O' . $last)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->freezePane('C' . self::FIRST_ROW);

        return $last;
    }

    /**
     * Sheet SUMMARY: rekap total dan per line, memakai rumus COUNTIFS
     * ke sheet bulan, seperti di file asli.
     */
    private function writeSummary(Worksheet $s, string $year, string $monthName, array $lines, int $last): void
    {
        $ref   = "'" . $monthName . "'!";
        $first = self::FIRST_ROW;
        $p     = self::SUM_FIRST;
        $q     = $p + max(count($lines), 1) - 1;

        $rng = static fn (string $col): string => "{$ref}\${$col}\${$first}:\${$col}\${$last}";
        $rA = $rng('A');
        $rC = $rng('C');
        $rD = $rng('D');
        $rE = $rng('E');
        $rF = $rng('F');
        $rH = $rng('H');
        $rJ = $rng('J');
        $rL = $rng('L');

        $s->setCellValue('F1', 'DATA REKAN ANDON ' . $year);
        $s->setCellValue('F2', self::COMPANY);
        $s->setCellValue('F3', self::AREA);
        $s->setCellValue('L4', date('d/m/Y'));
        $s->getStyle('F1:F3')->getFont()->setBold(true);

        $s->setCellValue('A5', 'SUMMARY');
        $s->mergeCells('A5:M5');
        $s->getStyle('A5')->getFont()->setBold(true)->setSize(13);

        $s->setCellValue('A8', 'SEMUA LINE');
        $s->mergeCells('A8:D8');
        $s->setCellValue('A9', 'KATEGORI');
        $s->setCellValue('C9', 'KONDISI');
        $s->setCellValue('D9', 'JUMLAH');

        $s->setCellValue('A10', 'KETERSEDIAAN');   $s->mergeCells('A10:B11');
        $s->setCellValue('C10', 'ADA');            $s->setCellValue('D10', "=SUM(D{$p}:D{$q})");
        $s->setCellValue('C11', 'TIDAK ADA');      $s->setCellValue('D11', "=SUM(E{$p}:E{$q})");

        $s->setCellValue('A12', 'KONDISI REMOTE'); $s->mergeCells('A12:B13');
        $s->setCellValue('C12', 'ON');             $s->setCellValue('D12', "=SUM(F{$p}:F{$q})");
        $s->setCellValue('C13', 'OFF');            $s->setCellValue('D13', "=SUM(G{$p}:G{$q})");

        $s->setCellValue('A14', 'VISUAL NOMOR');   $s->mergeCells('A14:B15');
        $s->setCellValue('C14', 'ADA');            $s->setCellValue('D14', "=SUM(H{$p}:H{$q})");
        $s->setCellValue('C15', 'TIDAK ADA');      $s->setCellValue('D15', "=SUM(I{$p}:I{$q})");

        $s->setCellValue('A16', 'LAMPU');
        $s->mergeCells('A16:A23');
        $lampu = [
            [16, 'Material', 'J'],
            [18, 'Mekanik',  'K'],
            [20, 'QC/QA',    'L'],
            [22, 'SPV',      'M'],
        ];
        foreach ($lampu as [$row, $label, $col]) {
            $s->setCellValue("B{$row}", $label);
            $s->mergeCells("B{$row}:B" . ($row + 1));
            $s->setCellValue("C{$row}", 'OFF');
            $s->setCellValue("D{$row}", "=COUNTIF({$col}{$p}:{$col}{$q},\"OFF\")");
            $s->setCellValue('C' . ($row + 1), 'ON');
            $s->setCellValue('D' . ($row + 1), "=COUNTIF({$col}{$p}:{$col}{$q},\"ON\")");
        }

        $s->setCellValue('A24', 'TOTAL STATION'); $s->mergeCells('A24:B24');
        $s->setCellValue('C24', '-');             $s->setCellValue('D24', "=SUM(C{$p}:C{$q})");
        $s->setCellValue('A25', 'TOTAL LINE');    $s->mergeCells('A25:B25');
        $s->setCellValue('C25', '-');             $s->setCellValue('D25', "=COUNTA(A{$p}:A{$q})");

        $s->setCellValue('A28', 'PERLINE');
        $s->setCellValue('A29', 'LINE');           $s->mergeCells('A29:B30');
        $s->setCellValue('C29', 'TOTAL STATION');  $s->mergeCells('C29:C30');
        $s->setCellValue('D29', 'TERSEDIA');       $s->mergeCells('D29:E29');
        $s->setCellValue('F29', 'KONDISI REMOTE'); $s->mergeCells('F29:G29');
        $s->setCellValue('H29', 'VISUAL NOMOR');   $s->mergeCells('H29:I29');
        $s->setCellValue('J29', 'LAMPU');          $s->mergeCells('J29:M29');

        $sub = [
            'D30' => 'ADA', 'E30' => 'TIDAK ADA',
            'F30' => 'ON',  'G30' => 'OFF',
            'H30' => 'ADA', 'I30' => 'TIDAK ADA',
            'J30' => 'MATERIAL', 'K30' => 'MEKANIK', 'L30' => 'QC/QA', 'M30' => 'SPV',
        ];
        foreach ($sub as $cell => $text) {
            $s->setCellValue($cell, $text);
        }

        foreach ($lines as $i => $line) {
            $r = $p + $i;
            $s->setCellValue("A{$r}", $line);
            $s->setCellValue("C{$r}", "=D{$r}+E{$r}");
            $s->setCellValue("D{$r}", "=COUNTIFS({$rA},\$A{$r},{$rC},\"ADA\")");
            $s->setCellValue("E{$r}", "=COUNTIFS({$rA},\$A{$r},{$rC},\"TIDAK ADA\")");
            $s->setCellValue("F{$r}", "=COUNTIFS({$rA},\$A{$r},{$rD},\"ON\")");
            $s->setCellValue("G{$r}", "=COUNTIFS({$rA},\$A{$r},{$rD},\"OFF\")");
            $s->setCellValue("H{$r}", "=COUNTIFS({$rA},\$A{$r},{$rE},\"ADA\")");
            $s->setCellValue("I{$r}", "=COUNTIFS({$rA},\$A{$r},{$rE},\"TIDAK ADA\")");
            $s->setCellValue("J{$r}", "=IF(COUNTIFS({$rA},\$A{$r},{$rF},\"OFF\")>0,\"OFF\",IF(COUNTIFS({$rA},\$A{$r},{$rF},\"ON\")>0,\"ON\",\"-\"))");
            $s->setCellValue("K{$r}", "=IF(COUNTIFS({$rA},\$A{$r},{$rH},\"OFF\")>0,\"OFF\",IF(COUNTIFS({$rA},\$A{$r},{$rH},\"ON\")>0,\"ON\",\"-\"))");
            $s->setCellValue("L{$r}", "=IF(COUNTIFS({$rA},\$A{$r},{$rJ},\"OFF\")>0,\"OFF\",IF(COUNTIFS({$rA},\$A{$r},{$rJ},\"ON\")>0,\"ON\",\"-\"))");
            $s->setCellValue("M{$r}", "=IF(COUNTIFS({$rA},\$A{$r},{$rL},\"OFF\")>0,\"OFF\",IF(COUNTIFS({$rA},\$A{$r},{$rL},\"ON\")>0,\"ON\",\"-\"))");
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $s->getStyle('A8:D9')->applyFromArray($headerStyle);
        $s->getStyle('A28:M30')->applyFromArray($headerStyle);

        $grid = ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]];
        $s->getStyle('A9:D25')->applyFromArray(['borders' => $grid, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $s->getStyle("A29:M{$q}")->applyFromArray(['borders' => $grid, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        $s->getColumnDimension('A')->setWidth(12);
        $s->getColumnDimension('B')->setWidth(10);
        $s->getColumnDimension('C')->setWidth(14);
        foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $col) {
            $s->getColumnDimension($col)->setWidth(12);
        }
    }
}
