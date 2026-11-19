<?php

namespace App\Models\Pendataan;

class AppUserModel extends PendataanModel
{
    protected $table      = 'app_users';
    protected $primaryKey = 'id_user';

    protected $allowedFields = [
        'id_role',
        'nomor_karyawan',
        'nama_user',
        'username',
        'password_hash',
        'email',
        'status',
        'last_login_at',
    ];
}
