<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRoles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'supervisor', 'user', 'pic_audit', 'petugas_area', 'verifikator', 'viewer'],
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'role']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_roles');

        // Role utama yang sudah ada langsung dipindahkan ke user_roles
        $this->db->query('INSERT IGNORE INTO user_roles (user_id, role, created_at) SELECT id, role, NOW() FROM users');
    }

    public function down()
    {
        $this->forge->dropTable('user_roles', true);
    }
}
