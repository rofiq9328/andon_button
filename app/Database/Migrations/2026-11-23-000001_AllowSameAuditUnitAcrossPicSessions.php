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
        foreach ($this->getUniqueIndexes() as $name => $columns) {
            if ($columns === self::OLD_COLUMNS) {
                $this->dropIndex($name);
            }
        }

        $hasNewIndex = false;
        foreach ($this->getUniqueIndexes() as $columns) {
            if ($columns === self::NEW_COLUMNS) {
                $hasNewIndex = true;
                break;
            }
        }

        if (!$hasNewIndex) {
            $this->createUniqueIndex(self::NEW_INDEX, self::NEW_COLUMNS);
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
             FROM ' . $this->tableIdentifier() . '
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
                $this->dropIndex($name);
            }
        }

        $this->createUniqueIndex(self::OLD_INDEX, self::OLD_COLUMNS);
    }

    /**
     * Ambil indeks unik selain PRIMARY KEY beserta urutan kolomnya.
     */
    private function getUniqueIndexes(): array
    {
        $indexes = [];

        if ($this->isSQLite()) {
            $tableName = $this->tableName();
            $rows = $this->db->query(
                'PRAGMA index_list(' . $this->db->escape($tableName) . ')'
            )->getResultArray();

            foreach ($rows as $row) {
                $name = $row['name'] ?? '';
                $isUnique = (int) ($row['unique'] ?? 0);

                if ($name === '' || $isUnique !== 1) {
                    continue;
                }

                $columnsRows = $this->db->query(
                    'PRAGMA index_info(' . $this->db->escape($name) . ')'
                )->getResultArray();

                $columns = [];
                foreach ($columnsRows as $columnRow) {
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
            $keyName = $row['Key_name'] ?? '';
            $nonUnique = (int) ($row['Non_unique'] ?? 1);

            if ($keyName === '' || strtoupper($keyName) === 'PRIMARY' || $nonUnique !== 0) {
                continue;
            }

            $sequence = (int) ($row['Seq_in_index'] ?? 0);
            $column = $row['Column_name'] ?? null;

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

    private function isSQLite(): bool
    {
        return stripos($this->db->DBDriver, 'SQLite3') !== false;
    }

    private function tableName(): string
    {
        return ($this->db->DBPrefix ?? '') . self::TABLE;
    }

    private function tableIdentifier(): string
    {
        return $this->db->protectIdentifiers(self::TABLE);
    }

    private function dropIndex(string $name): void
    {
        $index = $this->db->escapeIdentifiers($name);

        if ($this->isSQLite()) {
            $this->db->query('DROP INDEX ' . $index);
            return;
        }

        $this->db->query(
            'ALTER TABLE ' . $this->tableIdentifier() . ' DROP INDEX ' . $index
        );
    }

    private function createUniqueIndex(string $name, array $columns): void
    {
        $quotedColumns = array_map(
            function ($column) {
                return $this->db->escapeIdentifiers($column);
            },
            $columns
        );

        $this->db->query(
            'CREATE UNIQUE INDEX ' . $this->db->escapeIdentifiers($name)
            . ' ON ' . $this->tableIdentifier()
            . ' (' . implode(', ', $quotedColumns) . ')'
        );
    }
}
