<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditAndonDetailModel extends Model
{
    protected $table         = 'audit_andon_detail';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'audit_id', 'periode', 'line', 'line_no',
        'tersedia', 'kondisi_remote', 'visual_nomor',
        'material_lampu', 'material_layar',
        'mekanik_lampu', 'mekanik_layar',
        'qc_lampu', 'qc_layar',
        'spv_lampu', 'spv_layar',
        'perbaikan_rusak', 'ganti_baterai',
        'catatan', 'updated_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Nilai yang diizinkan sama dengan pilihan dropdown di file Excel.
     * Semua field permit_empty, karena draft boleh belum lengkap.
     */
    protected $validationRules = [
        'tersedia'         => 'permit_empty|in_list[ADA,TIDAK ADA]',
        'kondisi_remote'   => 'permit_empty|in_list[ON,OFF]',
        'visual_nomor'     => 'permit_empty|in_list[ADA,TIDAK ADA]',
        'material_lampu'   => 'permit_empty|in_list[ON,OFF]',
        'material_layar'   => 'permit_empty|in_list[MUNCUL,TIDAK]',
        'mekanik_lampu'    => 'permit_empty|in_list[ON,OFF]',
        'mekanik_layar'    => 'permit_empty|in_list[MUNCUL,TIDAK]',
        'qc_lampu'         => 'permit_empty|in_list[ON,OFF]',
        'qc_layar'         => 'permit_empty|in_list[MUNCUL,TIDAK]',
        'spv_lampu'        => 'permit_empty|in_list[ON,OFF]',
        'spv_layar'        => 'permit_empty|in_list[MUNCUL,TIDAK]',
        'perbaikan_rusak'  => 'permit_empty|in_list[YES,NO]',
        'ganti_baterai'    => 'permit_empty|in_list[YES,NO]',
    ];

    /**
     * Buat baris detail kosong untuk semua unit dalam daftar.
     * $units berisi hasil AuditAndonUnitModel::getUnitsInRange().
     */
    public function createForSession(int $auditId, string $periode, array $units): void
    {
        if (empty($units)) {
            return;
        }

        $rows = [];
        foreach ($units as $unit) {
            $rows[] = [
                'audit_id' => $auditId,
                'periode'  => $periode,
                'line'     => $unit['line'],
                'line_no'  => $unit['line_no'],
            ];
        }

        // ignore hanya unit yang sudah ada pada sesi audit yang sama.
        // Line yang sama boleh memiliki hasil audit terpisah pada sesi/PIC lain.
        $this->db->table($this->table)->ignore(true)->insertBatch($rows);
    }

    /**
     * Ambil daftar line dalam satu sesi tanpa memuat semua detail unit.
     */
    public function getLinesForSession(int $auditId): array
    {
        $rows = $this->db->table($this->table)
            ->select('line')
            ->distinct()
            ->where('audit_id', $auditId)
            ->orderBy('line', 'ASC')
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($rows, 'line'));
    }

    /**
     * Ambil detail hanya untuk line yang sedang dibuka.
     */
    public function getForSessionLine(int $auditId, int $line): array
    {
        return $this->where([
            'audit_id' => $auditId,
            'line'     => $line,
        ])->orderBy('line_no', 'ASC')->findAll();
    }

    /**
     * Ringkasan jumlah unit dan unit yang seluruh kolomnya sudah terisi.
     * Menghitung langsung di database agar halaman daftar line tidak
     * perlu mengambil semua baris detail ke memori PHP.
     */
    public function getProgressByLine(int $auditId): array
    {
        // Kolom RUSAK dan GANTI BATERAI adalah kolom tindakan:
        // kosong berarti tidak ada tindakan, YES hanya dipilih bila diperlukan.
        $requiredFields = [
            'tersedia', 'kondisi_remote', 'visual_nomor',
            'material_lampu', 'material_layar',
            'mekanik_lampu', 'mekanik_layar',
            'qc_lampu', 'qc_layar',
            'spv_lampu', 'spv_layar',
        ];

        $conditions = array_map(
            static function ($field) {
                return $field . ' IS NOT NULL';
            },
            $requiredFields
        );

        $completeCase = 'CASE WHEN ' . implode(' AND ', $conditions) . ' THEN 1 ELSE 0 END';

        return $this->db->table($this->table)
            ->select('line, COUNT(*) AS total, SUM(' . $completeCase . ') AS audited', false)
            ->where('audit_id', $auditId)
            ->groupBy('line')
            ->orderBy('line', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Semua unit untuk satu sesi, dikelompokkan per line.
     */
    public function getForSessionGrouped(int $auditId): array
    {
        $rows = $this->where('audit_id', $auditId)
            ->orderBy('line', 'ASC')
            ->orderBy('line_no', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['line']][] = $row;
        }

        return $grouped;
    }
}
