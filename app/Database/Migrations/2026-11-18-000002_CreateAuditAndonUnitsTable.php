<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditAndonUnitsTable extends Migration
{
    public function up()
    {
        // Master unit untuk MENU AUDIT, dipisah dari tabel button_id.
        // Dipakai untuk membuat baris detail saat sesi audit dibuat.
        // Diisi dari struktur sheet Excel (line 1-77). Tidak berelasi dengan button_id.
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
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
        $this->forge->addUniqueKey(['line', 'line_no']);
        $this->forge->createTable('audit_andon_units');
    }

    public function down()
    {
        $this->forge->dropTable('audit_andon_units', true);
    }
}
