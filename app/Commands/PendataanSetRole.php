<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PendataanSetRole extends BaseCommand
{
    protected $group       = 'Pendataan';
    protected $name        = 'pendataan:set-role';
    protected $description = 'Mengatur role user (termasuk role pendataan barang).';
    protected $usage       = 'pendataan:set-role <username> <role>';

    private const ROLES = ['admin', 'supervisor', 'user', 'pic_audit', 'petugas_area', 'verifikator', 'viewer'];

    public function run(array $params)
    {
        $username = $params[0] ?? null;
        $role     = $params[1] ?? null;

        if ($username === null || $role === null || !in_array($role, self::ROLES, true)) {
            CLI::error('Pemakaian: ' . $this->usage);
            CLI::write('Role: ' . implode(', ', self::ROLES));
            return;
        }

        $user = db_connect()->table('users')->where('username', $username)->get()->getRowArray();
        if ($user === null) {
            CLI::error("User '{$username}' tidak ditemukan.");
            return;
        }

        db_connect()->table('users')->where('id', $user['id'])->update(['role' => $role]);

        CLI::write("Role '{$username}' diubah menjadi {$role}.", 'green');
    }
}
