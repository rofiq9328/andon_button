<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // GANTI password ini setelah login pertama kali!
        $data = [
            'username'   => 'admin',
            'email'      => 'admin@example.com',
            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
            'full_name'  => 'Administrator',
            'role'       => 'admin',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}
