<?php

namespace App\Commands;

use App\Models\Pendataan\AppUserModel;
use App\Models\Pendataan\RoleModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PendataanBuatAdmin extends BaseCommand
{
    protected $group       = 'Pendataan';
    protected $name        = 'pendataan:buat-admin';
    protected $description = 'Membuat user admin untuk pendataan barang.';
    protected $usage       = 'pendataan:buat-admin <username> <password> [nama]';

    public function run(array $params)
    {
        $username = $params[0] ?? null;
        $password = $params[1] ?? null;

        if ($username === null || $password === null) {
            CLI::error('Pemakaian: ' . $this->usage);
            return;
        }

        $role = (new RoleModel())->where('kode_role', 'ADMIN')->first();
        if ($role === null) {
            CLI::error('Role ADMIN belum ada. Import SQL pendataan barang terlebih dahulu.');
            return;
        }

        (new AppUserModel())->insert([
            'id_role'       => $role['id_role'],
            'nama_user'     => $params[2] ?? $username,
            'username'      => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'status'        => 1,
        ]);

        CLI::write("Admin '{$username}' berhasil dibuat.", 'green');
    }
}
