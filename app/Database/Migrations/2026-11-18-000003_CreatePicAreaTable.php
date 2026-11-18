<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePicAreaTable extends Migration
{
    public function up()
    {
        // Satu PIC bisa punya beberapa baris (beberapa rentang line).
        // Rentang tidak boleh tumpang tindih antar PIC; dicek di controller/model.
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'line_from' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'line_to' => [
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
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pic_area');
    }

    public function down()
    {
        $this->forge->dropTable('pic_area', true);
    }
}
