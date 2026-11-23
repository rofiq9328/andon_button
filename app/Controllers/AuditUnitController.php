<?php

namespace App\Controllers;

class AuditUnitController extends BaseController
{
    /**
     * Master jumlah button per line.
     */
    public function index()
    {
        $lines = db_connect()->table('audit_andon_units')
            ->select('line, COUNT(*) AS total')
            ->groupBy('line')
            ->orderBy('line', 'ASC')
            ->get()
            ->getResultArray();

        $totalUnits = 0;
        foreach ($lines as $line) {
            $totalUnits += (int) $line['total'];
        }

        return view('audit/unit_master', [
            'lines'      => $lines,
            'totalLines' => count($lines),
            'totalUnits' => $totalUnits,
        ]);
    }

    /**
     * Atur jumlah button untuk satu line atau beberapa line sekaligus.
     *
     * Master unit hanya menjadi acuan sesi audit berikutnya.
     * Baris detail sesi yang sudah tersimpan tidak disentuh.
     */
    public function save()
    {
        $lineFrom = filter_var($this->request->getPost('line_from'), FILTER_VALIDATE_INT);
        $lineTo = filter_var($this->request->getPost('line_to'), FILTER_VALIDATE_INT);
        $buttonCount = filter_var($this->request->getPost('button_count'), FILTER_VALIDATE_INT);

        if (
            $lineFrom === false ||
            $lineTo === false ||
            $buttonCount === false ||
            $lineFrom < 1 ||
            $lineTo < $lineFrom ||
            $lineTo > 999 ||
            ($lineTo - $lineFrom + 1) > 100 ||
            $buttonCount < 1 ||
            $buttonCount > 200
        ) {
            return redirect()->to('audit-andon/master-button')
                ->withInput()
                ->with('error', 'Periksa kembali rentang line (maksimal 100 line) dan jumlah button (1–200).');
        }

        $db = db_connect();
        $toInsert = [];
        $added = 0;
        $removed = 0;
        $now = date('Y-m-d H:i:s');

        $db->transBegin();

        try {
            for ($line = (int) $lineFrom; $line <= (int) $lineTo; $line++) {
                $existingRows = $db->table('audit_andon_units')
                    ->select('line_no')
                    ->where('line', $line)
                    ->get()
                    ->getResultArray();

                $existingNumbers = [];
                foreach ($existingRows as $row) {
                    $existingNumbers[(int) $row['line_no']] = true;
                }

                // Hapus nomor di atas target hanya dari master.
                // Riwayat pemeriksaan berada di audit_andon_detail dan tetap aman.
                if (!empty($existingNumbers)) {
                    $db->table('audit_andon_units')
                        ->where('line', $line)
                        ->where('line_no >', (int) $buttonCount)
                        ->delete();

                    $removed += max(0, (int) $db->affectedRows());
                }

                // Pastikan nomor button berurutan mulai dari 1 sampai jumlah target.
                for ($lineNo = 1; $lineNo <= (int) $buttonCount; $lineNo++) {
                    if (!isset($existingNumbers[$lineNo])) {
                        $toInsert[] = [
                            'line'       => $line,
                            'line_no'    => $lineNo,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            if (!empty($toInsert)) {
                $db->table('audit_andon_units')->insertBatch($toInsert);
                $added = count($toInsert);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();

                return redirect()->to('audit-andon/master-button')
                    ->withInput()
                    ->with('error', 'Pengaturan jumlah button gagal disimpan.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Audit unit master update failed: ' . $e->getMessage());

            return redirect()->to('audit-andon/master-button')
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui master button audit.');
        }

        $lineTotal = (int) $lineTo - (int) $lineFrom + 1;

        return redirect()->to('audit-andon/master-button')
            ->with(
                'success',
                "Berhasil mengatur {$lineTotal} line. {$added} button ditambahkan dan {$removed} button dihapus dari master."
            );
    }
}
