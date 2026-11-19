<?php

namespace App\Models\Pendataan;

class RoleModel extends PendataanModel
{
    protected $table      = 'roles';
    protected $primaryKey = 'id_role';

    protected $allowedFields = [
        'kode_role',
        'nama_role',
        'deskripsi',
        'status',
    ];
}
