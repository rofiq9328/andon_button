<?php

namespace App\Models\Pendataan;

class AreaModel extends PendataanModel
{
    protected $table      = 'area';
    protected $primaryKey = 'id_area';

    protected $allowedFields = [
        'id_factory',
        'parent_area_id',
        'cakupan_area',
        'kode_area',
        'nama_area',
        'jenis_area',
        'fungsi_stok',
        'deskripsi',
        'status',
    ];
}
