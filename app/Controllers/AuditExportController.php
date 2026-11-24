<?php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Export data audit andon memakai file Excel asli di root project sebagai template.
 * Format, merge, lebar kolom, dan dropdown diambil dari template.
 * Data dan rumus SUMMARY diisi dari database.
 */
class AuditExportController extends BaseController
{
    private const TEMPLATE  = 'Audit andon november (new).xlsx';
    private const FIRST_ROW = 10;   // baris data pertama di sheet bulan
    private const SUM_FIRST = 31;   // baris data pertama di tabel PERLINE

    private const MONTHS = [
        'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
        'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER',
    ];

    /** Huruf kolom => field database, urut sesuai file Excel (A dan B diisi terpisah). */
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
    ];

    /** Pilihan dropdown tiap kolom, sama dengan file Excel asli. */
    private const DROPDOWNS = [
        'C' => 'ADA,TIDAK ADA',
        'D' => 'ON,OFF',
        'E' => 'ADA,TIDAK ADA',
        'F' => 'ON,OFF',
        'G' => 'MUNCUL,TIDAK',
        'H' => 'ON,OFF',
        'I' => 'MUNCUL,TIDAK',
        'J' => 'ON,OFF',
        'K' => 'MUNCUL,TIDAK',
        'L' => 'ON,OFF',
        'M' => 'MUNCUL,TIDAK',
        'N' => 'YES,NO',
        'O' => 'YES,NO',
    ];

    public function excel()
    {
        if (!class_exists(IOFactory::class)) {
            return redirect()->to('audit-andon')
                ->with('error', 'PhpSpreadsheet belum terpasang di server. Jalankan composer install.');
        }

        $templatePath = ROOTPATH . self::TEMPLATE;
        if (!is_file($templatePath)) {
            return redirect()->to('audit-andon')
                ->with('error', 'File template Excel tidak ditemukan di root project.');
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

            $spreadsheet = IOFactory::load($templatePath);

            $sheet   = $spreadsheet->getSheetByName('NOVEMBER') ?? $spreadsheet->getSheet(0);
            $summary = $spreadsheet->getSheetByName('SUMMARY');
            if ($summary === null) {
                throw new \RuntimeException('Sheet SUMMARY tidak ditemukan di template.');
            }

            $sheet->setTitle($monthName);
            $this->fillUnits($sheet, $year, $units, $details);
            $this->fillSummary($summary, $year, $monthName, $units);

            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            // Rumus tidak dihitung di server. Excel menghitungnya saat file dibuka.
            $writer->setPreCalculateFormulas(false);

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
     * Isi sheet bulan. Format dari template dipertahankan.
     */
    private function fillUnits(Worksheet $sheet, string $year, array $units, array $details): void
    {
        $sheet->setCellValue('H1', 'DATA REKAN ANDON ' . $year);
        $sheet->setCellValue('N4', date('d/m/Y'));

        // Kosongkan data lama dari template, format tetap dipertahankan
        $oldLast = max($sheet->getHighestRow(), self::FIRST_ROW);
        for ($r = self::FIRST_ROW; $r <= $oldLast; $r++) {
            foreach (range('A', 'O') as $col) {
                $sheet->getCell($col . $r)->setValueExplicit(null, DataType::TYPE_NULL);
            }
        }

        $fields = array_values(self::COLUMNS);
        $rows   = [];

        foreach ($units as $u) {
            $d   = $details[$u['line'] . '-' . $u['line_no']] ?? [];
            $row = [(int) $u['line'], (int) $u['line_no']];

            foreach ($fields as $field) {
                // Kolom tindakan: kosong di form berarti tidak perlu tindakan (NO).
                if (in_array($field, ['perbaikan_rusak', 'ganti_baterai'], true)) {
                    $row[] = $d[$field] ?? 'NO';
                } else {
                    $row[] = $d[$field] ?? null;
                }
            }

            $rows[] = $row;
        }

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A' . self::FIRST_ROW, false);
        }

        $lastRow = self::FIRST_ROW + max(count($units), 1) - 1;

        // Hapus warna merah tetap dari template (sisa data lama)
        for ($r = self::FIRST_ROW; $r <= $lastRow; $r++) {
            foreach (range('A', 'O') as $col) {
                $fill = $sheet->getCell($col . $r)->getStyle()->getFill();
                if ($fill->getFillType() !== Fill::FILL_NONE && $fill->getStartColor()->getARGB() === 'FFFFC7CE') {
                    $fill->setFillType(Fill::FILL_NONE);
                }
            }
        }

        // Aturan kondisional dari template dipertahankan untuk semua line
        $this->restrictConditionalFormats($sheet, $lastRow);

        // Kolom LINE dan LINE NO tanpa warna latar
        $sheet->getStyle('A' . self::FIRST_ROW . ':B' . $lastRow)->getFill()->setFillType(Fill::FILL_NONE);

        // Sel bermasalah diberi merah langsung dari nilainya, untuk semua line.
        $bad = [
            'C' => 'TIDAK ADA', 'D' => 'OFF', 'E' => 'TIDAK ADA',
            'F' => 'OFF', 'G' => 'TIDAK', 'H' => 'OFF', 'I' => 'TIDAK',
            'J' => 'OFF', 'K' => 'TIDAK', 'L' => 'OFF', 'M' => 'TIDAK',
            'N' => 'YES', 'O' => 'YES',
        ];

        foreach ($units as $i => $u) {
            $r = self::FIRST_ROW + $i;
            $d = $details[$u['line'] . '-' . $u['line_no']] ?? [];

            foreach ($bad as $col => $value) {
                if (($d[self::COLUMNS[$col]] ?? null) === $value) {
                    $cell = $sheet->getStyle($col . $r);
                    $cell->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                    $cell->getFont()->getColor()->setARGB('FF9C0006');
                }
            }
        }

        // Dropdown ditulis ulang per sel, supaya susunan yang benar berlaku di semua baris
        $last = self::FIRST_ROW + max(count($units), 1) - 1;
        foreach (self::DROPDOWNS as $col => $list) {
            $dv = new DataValidation();
            $dv->setType(DataValidation::TYPE_LIST);
            $dv->setErrorStyle(DataValidation::STYLE_STOP);
            $dv->setAllowBlank(true);
            $dv->setFormula1('"' . $list . '"');

            for ($r = self::FIRST_ROW; $r <= $last; $r++) {
                $sheet->getCell($col . $r)->setDataValidation(clone $dv);
            }
        }

        $sheet->freezePane('C' . self::FIRST_ROW);
    }

    /**
     * Isi sheet SUMMARY dengan rumus yang merujuk ke sheet bulan.
     * Semua line ditampilkan, mengikuti susunan kolom yang benar.
     */
    private function fillSummary(Worksheet $s, string $year, string $monthName, array $units): void
    {
        $lines = array_values(array_unique(array_map(static fn ($u) => (int) $u['line'], $units)));
        $p     = self::SUM_FIRST;
        $q     = $p + max(count($lines), 1) - 1;
        $last  = self::FIRST_ROW + max(count($units), 1) - 1;

        $rng = static fn (string $c): string => "'" . $monthName . "'!\$" . $c . '$' . self::FIRST_ROW . ':$' . $c . '$' . $last;
        $rA = $rng('A');
        $rC = $rng('C');
        $rD = $rng('D');
        $rE = $rng('E');
        $rF = $rng('F');
        $rH = $rng('H');
        $rJ = $rng('J');
        $rL = $rng('L');

        // Lepas merge di area per-line, karena jumlah dan urutan line berubah
        foreach ($s->getMergeCells() as $range) {
            [$start] = explode(':', $range);
            if ((int) preg_replace('/\D/', '', $start) >= $p) {
                $s->unmergeCells($range);
            }
        }

        // Kosongkan isi lama area per-line
        $clearTo = max($q, $s->getHighestRow());
        for ($r = $p; $r <= $clearTo; $r++) {
            foreach (['A', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $col) {
                $s->getCell($col . $r)->setValueExplicit(null, DataType::TYPE_NULL);
            }
        }

        // Format baris per-line mengikuti format baris 33 di template
        if ($q >= $p) {
            $s->duplicateStyle($s->getStyle('A33:M33'), "A{$p}:M{$q}");
        }

        $s->setCellValue('F1', 'DATA REKAN ANDON ' . $year);
        $s->setCellValue('L4', date('d/m/Y'));

        // Rekap total
        $s->setCellValue('D10', "=SUM(D{$p}:D{$q})");
        $s->setCellValue('D11', "=SUM(E{$p}:E{$q})");
        $s->setCellValue('D12', "=SUM(F{$p}:F{$q})");
        $s->setCellValue('D13', "=SUM(G{$p}:G{$q})");
        $s->setCellValue('D14', "=SUM(H{$p}:H{$q})");
        $s->setCellValue('D15', "=SUM(I{$p}:I{$q})");
        $s->setCellValue('D16', "=COUNTIF(J{$p}:J{$q},\"OFF\")");
        $s->setCellValue('D17', "=COUNTIF(J{$p}:J{$q},\"ON\")");
        $s->setCellValue('D18', "=COUNTIF(K{$p}:K{$q},\"OFF\")");
        $s->setCellValue('D19', "=COUNTIF(K{$p}:K{$q},\"ON\")");
        $s->setCellValue('D20', "=COUNTIF(L{$p}:L{$q},\"OFF\")");
        $s->setCellValue('D21', "=COUNTIF(L{$p}:L{$q},\"ON\")");
        $s->setCellValue('D22', "=COUNTIF(M{$p}:M{$q},\"OFF\")");
        $s->setCellValue('D23', "=COUNTIF(M{$p}:M{$q},\"ON\")");
        $s->setCellValue('D24', "=SUM(C{$p}:C{$q})");
        $s->setCellValue('D25', "=COUNTA(A{$p}:A{$q})");

        // Rekap per line
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
    }

    /**
     * Hapus aturan pewarnaan bawaan template. Kalau struktur library berbeda,
     * aturan dibiarkan agar file tidak rusak.
     */
    private function clearConditionalFormats(Worksheet $sheet): void
    {
        try {
            $prop = new \ReflectionProperty(Worksheet::class, 'conditionalStylesCollection');
            $prop->setAccessible(true);
            $prop->setValue($sheet, []);
        } catch (\ReflectionException $e) {
            log_message('warning', 'Aturan pewarnaan template tidak dapat dihapus: ' . $e->getMessage());
        }
    }

    /**
     * Aturan kondisional dari template dipertahankan, tetapi rentangnya
     * dibatasi sampai baris $lastRow (line 68-77 tidak diberi warna).
     */
    private function restrictConditionalFormats(Worksheet $sheet, int $lastRow): void
    {
        $collection = $sheet->getConditionalStylesCollection();
        if (empty($collection)) {
            return;
        }

        $this->clearConditionalFormats($sheet);

        foreach ($collection as $range => $rules) {
            foreach (explode(' ', trim((string) $range)) as $part) {
                if (!preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $part, $m)) {
                    continue;
                }

                $startRow = (int) $m[2];
                $endRow   = min((int) $m[4], $lastRow);

                if ($startRow > $endRow) {
                    continue;
                }

                $sheet->setConditionalStyles($m[1] . $startRow . ':' . $m[3] . $endRow, $rules);
            }
        }
    }
}
