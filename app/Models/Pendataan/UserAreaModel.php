<?php

namespace App\Models\Pendataan;

class UserAreaModel extends PendataanModel
{
    protected $table      = 'user_area';
    protected $primaryKey = 'id_user_area';

    protected $allowedFields = [
        'id_user',
        'id_area',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'assigned_by',
    ];
}
