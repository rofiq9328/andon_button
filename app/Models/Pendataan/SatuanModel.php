<?php

namespace App\Models\Pendataan;

class SatuanModel extends PendataanModel
{
    protected $table      = 'satuan';
    protected $primaryKey = 'id_satuan';

    protected $allowedFields = [
        'kode_satuan',
        'nama_satuan',
        'status',
    ];
}
