<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPendataanRoles extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'supervisor', 'user', 'pic_audit', 'petugas_area', 'verifikator', 'viewer'],
                'default'    => 'user',
            ],
        ]);
    }

    public function down()
    {
        $this->db->table('users')
            ->whereIn('role', ['petugas_area', 'verifikator', 'viewer'])
            ->update(['role' => 'user']);

        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'supervisor', 'user', 'pic_audit'],
                'default'    => 'user',
            ],
        ]);
    }
}
