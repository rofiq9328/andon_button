<?php

namespace App\Models\Pendataan;

class AturanStokAreaModel extends PendataanModel
{
    protected $table      = 'aturan_stok_area';
    protected $primaryKey = 'id_aturan_stok';

    protected $allowedFields = [
        'id_area',
        'id_barang',
        'stok_minimum',
        'stok_ideal',
        'stok_maksimum',
        'status',
        'created_by',
        'updated_by',
    ];
}
