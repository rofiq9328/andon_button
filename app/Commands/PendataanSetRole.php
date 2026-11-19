<?php

namespace App\Commands;

use App\Models\UserRoleModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PendataanSetRole extends BaseCommand
{
    protected $group       = 'Pendataan';
    protected $name        = 'pendataan:set-role';
    protected $description = 'Menambahkan satu role ke user yang sudah ada.';
    protected $usage       = 'pendataan:set-role <username> <role>';

    public function run(array $params)
    {
        $username = $params[0] ?? null;
        $role     = $params[1] ?? null;

        if ($username === null || $role === null || !in_array($role, UserRoleModel::ALL_ROLES, true)) {
            CLI::error('Pemakaian: ' . $this->usage);
            CLI::write('Role: ' . implode(', ', UserRoleModel::ALL_ROLES));
            return;
        }

        $user = db_connect()->table('users')->where('username', $username)->get()->getRowArray();
        if ($user === null) {
            CLI::error("User '{$username}' tidak ditemukan.");
            return;
        }

        $model = new UserRoleModel();
        $sudahAda = $model->where(['user_id' => $user['id'], 'role' => $role])->first();
        if ($sudahAda === null) {
            $model->insert(['user_id' => $user['id'], 'role' => $role]);
        }

        CLI::write("Role '{$role}' ditambahkan ke '{$username}'.", 'green');
    }
}
