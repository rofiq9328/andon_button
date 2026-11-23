<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/**
 * Enforce satu nomor button unik per sesi audit, bukan per periode.
 *
 * Migration ini menegaskan ulang perubahan indeks supaya database yang
 * sudah pernah menjalankan migration sebelumnya juga mendapatkan struktur
 * indeks yang benar.
 */
class EnsureAuditAndonDetailUniquePerSession extends Migration
{
    private const TABLE = 'audit_andon_detail';
    private const EXPECTED_COLUMNS = ['audit_id', 'line', 'line_no'];
    private const INDEX_NAME = 'uniq_audit_session_line_unit';

    public function up()
    {
        $indexes = $this->getUniqueIndexes();
        $hasExpectedIndex = false;

        foreach ($indexes as $name => $columns) {
            if ($columns === self::EXPECTED_COLUMNS) {
                $hasExpectedIndex = true;
                continue;
            }

            // Tabel detail audit hanya memerlukan satu unique key bisnis:
            // satu button per sesi + line + nomor button.
            $this->dropIndex($name);
        }

        if (!$hasExpectedIndex) {
            $this->createExpectedIndex();
        }
    }

    public function down()
    {
        $duplicate = $this->db->query(
            'SELECT 1
             FROM ' . $this->tableIdentifier() . '
             GROUP BY periode, line, line_no
             HAVING COUNT(*) > 1
             LIMIT 1'
        )->getRowArray();

        if ($duplicate) {
            throw new RuntimeException(
                'Rollback tidak dapat dilakukan karena sudah ada hasil audit lintas sesi untuk line/button yang sama.'
            );
        }

        foreach ($this->getUniqueIndexes() as $name => $columns) {
            if ($columns === self::EXPECTED_COLUMNS) {
                $this->dropIndex($name);
            }
        }

        $this->db->query(
            'CREATE UNIQUE INDEX ' . $this->db->escapeIdentifiers('uniq_audit_periode_line_unit')
            . ' ON ' . $this->tableIdentifier()
            . ' (' . $this->db->escapeIdentifiers('periode')
            . ', ' . $this->db->escapeIdentifiers('line')
            . ', ' . $this->db->escapeIdentifiers('line_no') . ')'
        );
    }

    private function getUniqueIndexes(): array
    {
        $indexes = [];

        if (stripos($this->db->DBDriver, 'SQLite3') !== false) {
            $table = ($this->db->DBPrefix ?? '') . self::TABLE;
            $rows = $this->db->query(
                'PRAGMA index_list(' . $this->db->escape($table) . ')'
            )->getResultArray();

            foreach ($rows as $row) {
                $name = $row['name'] ?? '';
                if ($name === '' || (int) ($row['unique'] ?? 0) !== 1) {
                    continue;
                }

                $columnRows = $this->db->query(
                    'PRAGMA index_info(' . $this->db->escape($name) . ')'
                )->getResultArray();

                $columns = [];
                foreach ($columnRows as $columnRow) {
                    $columns[(int) $columnRow['seqno']] = $columnRow['name'];
                }
                ksort($columns);
                $indexes[$name] = array_values($columns);
            }

            return $indexes;
        }

        $rows = $this->db->query(
            'SHOW INDEX FROM ' . $this->tableIdentifier()
        )->getResultArray();

        foreach ($rows as $row) {
            $name = $row['Key_name'] ?? '';
            $nonUnique = (int) ($row['Non_unique'] ?? 1);

            if ($name === '' || strtoupper($name) === 'PRIMARY' || $nonUnique !== 0) {
                continue;
            }

            $sequence = (int) ($row['Seq_in_index'] ?? 0);
            $column = $row['Column_name'] ?? null;

            if ($sequence > 0 && $column !== null) {
                $indexes[$name][$sequence] = $column;
            }
        }

        foreach ($indexes as $name => $columns) {
            ksort($columns);
            $indexes[$name] = array_values($columns);
        }

        return $indexes;
    }

    private function tableIdentifier(): string
    {
        return $this->db->protectIdentifiers(self::TABLE);
    }

    private function dropIndex(string $name): void
    {
        $index = $this->db->escapeIdentifiers($name);

        if (stripos($this->db->DBDriver, 'SQLite3') !== false) {
            $this->db->query('DROP INDEX ' . $index);
            return;
        }

        $this->db->query(
            'ALTER TABLE ' . $this->tableIdentifier() . ' DROP INDEX ' . $index
        );
    }

    private function createExpectedIndex(): void
    {
        $columns = array_map(
            function ($column) {
                return $this->db->escapeIdentifiers($column);
            },
            self::EXPECTED_COLUMNS
        );

        $this->db->query(
            'CREATE UNIQUE INDEX ' . $this->db->escapeIdentifiers(self::INDEX_NAME)
            . ' ON ' . $this->tableIdentifier()
            . ' (' . implode(', ', $columns) . ')'
        );
    }
}
