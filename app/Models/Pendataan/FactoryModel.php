<?php

namespace App\Models\Pendataan;

class FactoryModel extends PendataanModel
{
    protected $table      = 'factory';
    protected $primaryKey = 'id_factory';

    protected $allowedFields = [
        'kode_factory',
        'nama_factory',
        'lokasi',
        'status',
    ];
}
