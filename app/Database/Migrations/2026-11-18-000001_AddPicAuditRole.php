<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPicAuditRole extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'supervisor', 'user', 'pic_audit'],
                'default'    => 'user',
            ],
        ]);
    }

    public function down()
    {
        // Turunkan dulu user PIC ke 'user' supaya tidak ada baris dengan
        // nilai role yang sudah tidak ada di ENUM.
        $this->db->table('users')
            ->where('role', 'pic_audit')
            ->update(['role' => 'user']);

        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'supervisor', 'user'],
                'default'    => 'user',
            ],
        ]);
    }
}
