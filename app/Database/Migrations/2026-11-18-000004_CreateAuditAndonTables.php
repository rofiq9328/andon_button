<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditAndonTables extends Migration
{
    public function up()
    {
        // ------------------------------------------------------------
        // Header: satu sesi per PIC per periode (bulan).
        // user_id dibuat nullable + SET NULL supaya riwayat audit tetap ada
        // walaupun akun PIC dihapus.
        // ------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'periode' => [
                'type'       => 'CHAR',
                'constraint' => 7,          // format 'YYYY-MM', contoh '2026-11'
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'submitted'],
                'default'    => 'draft',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // Diisi admin untuk membuka kembali sesi yang sudah terkunci
            // setelah akhir bulan. NULL = ikut aturan kunci per periode.
            'reopened_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['periode', 'user_id']);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_andon');

        // ------------------------------------------------------------
        // Detail: satu baris per unit remote per periode.
        // Semua kolom isian dibuat NULL supaya bisa disimpan sebagai draft
        // (NULL = belum diisi). Nilai ENUM mengikuti dropdown di Excel.
        // ------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'audit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'periode' => [
                'type'       => 'CHAR',
                'constraint' => 7,
            ],
            'line' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'line_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tersedia' => [
                'type'       => 'ENUM',
                'constraint' => ['ADA', 'TIDAK ADA'],
                'null'       => true,
            ],
            'kondisi_remote' => [
                'type'       => 'ENUM',
                'constraint' => ['ON', 'OFF'],
                'null'       => true,
            ],
            'visual_nomor' => [
                'type'       => 'ENUM',
                'constraint' => ['ADA', 'TIDAK ADA'],
                'null'       => true,
            ],
            'material_lampu' => [
                'type'       => 'ENUM',
                'constraint' => ['ON', 'OFF'],
                'null'       => true,
            ],
            'material_layar' => [
                'type'       => 'ENUM',
                'constraint' => ['MUNCUL', 'TIDAK'],
                'null'       => true,
            ],
            'mekanik_lampu' => [
                'type'       => 'ENUM',
                'constraint' => ['ON', 'OFF'],
                'null'       => true,
            ],
            'mekanik_layar' => [
                'type'       => 'ENUM',
                'constraint' => ['MUNCUL', 'TIDAK'],
                'null'       => true,
            ],
            'qc_lampu' => [
                'type'       => 'ENUM',
                'constraint' => ['ON', 'OFF'],
                'null'       => true,
            ],
            'qc_layar' => [
                'type'       => 'ENUM',
                'constraint' => ['MUNCUL', 'TIDAK'],
                'null'       => true,
            ],
            'spv_lampu' => [
                'type'       => 'ENUM',
                'constraint' => ['ON', 'OFF'],
                'null'       => true,
            ],
            'spv_layar' => [
                'type'       => 'ENUM',
                'constraint' => ['MUNCUL', 'TIDAK'],
                'null'       => true,
            ],
            'perbaikan_rusak' => [
                'type'       => 'ENUM',
                'constraint' => ['YES', 'NO'],
                'null'       => true,
            ],
            'ganti_baterai' => [
                'type'       => 'ENUM',
                'constraint' => ['YES', 'NO'],
                'null'       => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        // Satu unit hanya boleh diaudit sekali per periode
        $this->forge->addUniqueKey(['periode', 'line', 'line_no']);
        $this->forge->addKey('audit_id');
        $this->forge->addForeignKey('audit_id', 'audit_andon', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_andon_detail');
    }

    public function down()
    {
        // Detail dihapus dulu karena punya foreign key ke audit_andon
        $this->forge->dropTable('audit_andon_detail', true);
        $this->forge->dropTable('audit_andon', true);
    }
}
