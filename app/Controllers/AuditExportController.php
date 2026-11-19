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
 *
 * SUMMARY berisi nilai hasil hitungan PHP, bukan rumus, supaya file
 * tidak perlu menghitung ulang rumus saat dibuat.
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
            $this->writeUnits($sheet, $units, $details);

            $summary = $spreadsheet->createSheet();
            $summary->setTitle('SUMMARY');
            $this->writeSummary($summary, $year, $units, $details);

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
     * Isi baris unit (mulai baris 10) sekaligus.
     */
    private function writeUnits(Worksheet $sheet, array $units, array $details): void
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

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A' . self::FIRST_ROW, false);

            $last = self::FIRST_ROW + count($rows) - 1;
            $sheet->getStyle('A' . self::FIRST_ROW . ':O' . $last)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->freezePane('C' . self::FIRST_ROW);
    }

    /**
     * Sheet SUMMARY: rekap total dan per line, dihitung di PHP.
     */
    private function writeSummary(Worksheet $s, string $year, array $units, array $details): void
    {
        // Kumpulkan hitungan per line
        $stat = [];
        foreach ($units as $u) {
            $line = (int) $u['line'];
            $d    = $details[$u['line'] . '-' . $u['line_no']] ?? [];

            $st = $stat[$line] ?? [
                'ada' => 0, 'tidak' => 0, 'on' => 0, 'off' => 0,
                'vada' => 0, 'vtidak' => 0,
                'material' => [], 'mekanik' => [], 'qc' => [], 'spv' => [],
            ];

            if (($d['tersedia'] ?? null) === 'ADA')        { $st['ada']++; }
            if (($d['tersedia'] ?? null) === 'TIDAK ADA')  { $st['tidak']++; }
            if (($d['kondisi_remote'] ?? null) === 'ON')   { $st['on']++; }
            if (($d['kondisi_remote'] ?? null) === 'OFF')  { $st['off']++; }
            if (($d['visual_nomor'] ?? null) === 'ADA')    { $st['vada']++; }
            if (($d['visual_nomor'] ?? null) === 'TIDAK ADA') { $st['vtidak']++; }

            $st['material'][] = $d['material_lampu'] ?? null;
            $st['mekanik'][]  = $d['mekanik_lampu'] ?? null;
            $st['qc'][]       = $d['qc_lampu'] ?? null;
            $st['spv'][]      = $d['spv_lampu'] ?? null;

            $stat[$line] = $st;
        }

        $lampuStatus = static function (array $values): string {
            if (in_array('OFF', $values, true)) {
                return 'OFF';
            }
            if (in_array('ON', $values, true)) {
                return 'ON';
            }
            return '-';
        };

        // Total keseluruhan
        $tot = ['ada' => 0, 'tidak' => 0, 'on' => 0, 'off' => 0, 'vada' => 0, 'vtidak' => 0, 'station' => 0];
        $stLampu = ['material' => ['OFF' => 0, 'ON' => 0], 'mekanik' => ['OFF' => 0, 'ON' => 0],
                    'qc' => ['OFF' => 0, 'ON' => 0], 'spv' => ['OFF' => 0, 'ON' => 0]];

        foreach ($stat as $st) {
            foreach (['ada', 'tidak', 'on', 'off', 'vada', 'vtidak'] as $k) {
                $tot[$k] += $st[$k];
            }
            $tot['station'] += $st['ada'] + $st['tidak'];

            foreach (['material', 'mekanik', 'qc', 'spv'] as $k) {
                $status = $lampuStatus($st[$k]);
                if ($status !== '-') {
                    $stLampu[$k][$status]++;
                }
            }
        }

        // Judul
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
        $s->setCellValue('C10', 'ADA');            $s->setCellValue('D10', $tot['ada']);
        $s->setCellValue('C11', 'TIDAK ADA');      $s->setCellValue('D11', $tot['tidak']);

        $s->setCellValue('A12', 'KONDISI REMOTE'); $s->mergeCells('A12:B13');
        $s->setCellValue('C12', 'ON');             $s->setCellValue('D12', $tot['on']);
        $s->setCellValue('C13', 'OFF');            $s->setCellValue('D13', $tot['off']);

        $s->setCellValue('A14', 'VISUAL NOMOR');   $s->mergeCells('A14:B15');
        $s->setCellValue('C14', 'ADA');            $s->setCellValue('D14', $tot['vada']);
        $s->setCellValue('C15', 'TIDAK ADA');      $s->setCellValue('D15', $tot['vtidak']);

        $s->setCellValue('A16', 'LAMPU');
        $s->mergeCells('A16:A23');
        $lampu = [
            [16, 'Material', 'material'],
            [18, 'Mekanik',  'mekanik'],
            [20, 'QC/QA',    'qc'],
            [22, 'SPV',      'spv'],
        ];
        foreach ($lampu as [$row, $label, $key]) {
            $s->setCellValue("B{$row}", $label);
            $s->mergeCells("B{$row}:B" . ($row + 1));
            $s->setCellValue("C{$row}", 'OFF');
            $s->setCellValue("D{$row}", $stLampu[$key]['OFF']);
            $s->setCellValue('C' . ($row + 1), 'ON');
            $s->setCellValue('D' . ($row + 1), $stLampu[$key]['ON']);
        }

        $s->setCellValue('A24', 'TOTAL STATION'); $s->mergeCells('A24:B24');
        $s->setCellValue('C24', '-');             $s->setCellValue('D24', $tot['station']);
        $s->setCellValue('A25', 'TOTAL LINE');    $s->mergeCells('A25:B25');
        $s->setCellValue('C25', '-');             $s->setCellValue('D25', count($stat));

        // Rekap per line
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

        $i = 0;
        $last = self::SUM_FIRST - 1;
        foreach ($stat as $line => $st) {
            $r = self::SUM_FIRST + $i;
            $last = $r;
            $i++;

            $s->setCellValue("A{$r}", $line);
            $s->setCellValue("C{$r}", $st['ada'] + $st['tidak']);
            $s->setCellValue("D{$r}", $st['ada']);
            $s->setCellValue("E{$r}", $st['tidak']);
            $s->setCellValue("F{$r}", $st['on']);
            $s->setCellValue("G{$r}", $st['off']);
            $s->setCellValue("H{$r}", $st['vada']);
            $s->setCellValue("I{$r}", $st['vtidak']);
            $s->setCellValue("J{$r}", $lampuStatus($st['material']));
            $s->setCellValue("K{$r}", $lampuStatus($st['mekanik']));
            $s->setCellValue("L{$r}", $lampuStatus($st['qc']));
            $s->setCellValue("M{$r}", $lampuStatus($st['spv']));
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $s->getStyle('A8:D9')->applyFromArray($headerStyle);
        $s->getStyle('A28:M30')->applyFromArray($headerStyle);

        $grid = ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]];
        $center = ['horizontal' => Alignment::HORIZONTAL_CENTER];
        $s->getStyle('A9:D25')->applyFromArray(['borders' => $grid, 'alignment' => $center]);
        $s->getStyle('A29:M' . max($last, self::SUM_FIRST))->applyFromArray(['borders' => $grid, 'alignment' => $center]);

        $s->getColumnDimension('A')->setWidth(12);
        $s->getColumnDimension('B')->setWidth(10);
        $s->getColumnDimension('C')->setWidth(14);
        foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $col) {
            $s->getColumnDimension($col)->setWidth(12);
        }
    }
}
