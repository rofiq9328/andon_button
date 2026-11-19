<?php

namespace App\Models\Pendataan;

class StandarBarangAreaModel extends PendataanModel
{
    protected $table      = 'standar_barang_area';
    protected $primaryKey = 'id_standar';

    protected $allowedFields = [
        'id_area',
        'id_barang',
        'jumlah_standar',
        'berlaku_mulai',
        'berlaku_sampai',
        'keterangan',
        'status',
        'created_by',
    ];
}
