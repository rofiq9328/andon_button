<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFungsiStokToArea extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE area
            ADD COLUMN fungsi_stok ENUM('PENGGUNA', 'PENYIMPANAN', 'KEDUANYA')
            NOT NULL DEFAULT 'PENGGUNA'
            COMMENT 'Fungsi area untuk pengelolaan stok'
            AFTER jenis_area
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE area DROP COLUMN fungsi_stok");
    }
}
