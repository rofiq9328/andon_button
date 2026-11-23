<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AllowSameAuditUnitAcrossPicSessions extends Migration
{
    private const TABLE = 'audit_andon_detail';
    private const OLD_COLUMNS = ['periode', 'line', 'line_no'];
    private const NEW_COLUMNS = ['audit_id', 'line', 'line_no'];
    private const NEW_INDEX = 'uniq_audit_session_line_unit';
    private const OLD_INDEX = 'uniq_audit_periode_line_unit';

    /**
     * Satu line boleh diaudit oleh PIC berbeda pada periode yang sama.
     * Setiap sesi memiliki salinan detail sendiri agar hasil audit lama tetap utuh.
     */
    public function up()
    {
        $indexes = $this->getUniqueIndexes();

        foreach ($indexes as $name => $columns) {
            if ($columns === self::OLD_COLUMNS) {
                $this->forge->dropKey(self::TABLE, $name);
            }
        }

        $indexes = $this->getUniqueIndexes();
        $hasNewIndex = false;

        foreach ($indexes as $columns) {
            if ($columns === self::NEW_COLUMNS) {
                $hasNewIndex = true;
                break;
            }
        }

        if (!$hasNewIndex) {
            $this->forge->addUniqueKey(self::NEW_COLUMNS, self::NEW_INDEX);
            $this->forge->processIndexes(self::TABLE);
        }
    }

    /**
     * Kembalikan indeks lama hanya jika belum ada data lintas sesi
     * yang memakai kombinasi periode + line + nomor button yang sama.
     */
    public function down()
    {
        $duplicate = $this->db->query(
            'SELECT 1
             FROM audit_andon_detail
             GROUP BY periode, line, line_no
             HAVING COUNT(*) > 1
             LIMIT 1'
        )->getRowArray();

        if ($duplicate) {
            throw new RuntimeException(
                'Rollback tidak dapat dilakukan: sudah ada hasil audit untuk line/button yang sama di lebih dari satu sesi.'
            );
        }

        foreach ($this->getUniqueIndexes() as $name => $columns) {
            if ($columns === self::NEW_COLUMNS) {
                $this->forge->dropKey(self::TABLE, $name);
            }
        }

        $this->forge->addUniqueKey(self::OLD_COLUMNS, self::OLD_INDEX);
        $this->forge->processIndexes(self::TABLE);
    }

    /**
     * Ambil seluruh indeks unik selain PRIMARY KEY beserta urutan kolomnya.
     */
    private function getUniqueIndexes(): array
    {
        $rows = $this->db->query('SHOW INDEX FROM audit_andon_detail')->getResultArray();
        $indexes = [];

        foreach ($rows as $row) {
            $keyName = $row['Key_name'] ?? $row['key_name'] ?? '';
            $nonUnique = (int) ($row['Non_unique'] ?? $row['non_unique'] ?? 1);

            if ($keyName === '' || strtoupper($keyName) === 'PRIMARY' || $nonUnique !== 0) {
                continue;
            }

            $sequence = (int) ($row['Seq_in_index'] ?? $row['seq_in_index'] ?? 0);
            $column = $row['Column_name'] ?? $row['column_name'] ?? null;

            if ($sequence > 0 && $column !== null) {
                $indexes[$keyName][$sequence] = $column;
            }
        }

        foreach ($indexes as $name => $columns) {
            ksort($columns);
            $indexes[$name] = array_values($columns);
        }

        return $indexes;
    }
}
