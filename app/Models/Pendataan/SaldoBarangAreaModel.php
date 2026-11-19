<?php

namespace App\Models\Pendataan;

class SaldoBarangAreaModel extends PendataanModel
{
    protected $table      = 'saldo_barang_area';
    protected $primaryKey = 'id_saldo';

    protected $allowedFields = [
        'id_area',
        'id_barang',
        'kondisi',
        'jumlah',
    ];

    protected $useTimestamps = false;
}
