<?php

namespace App\Models\Pendataan;

class BarangModel extends PendataanModel
{
    protected $table      = 'barang';
    protected $primaryKey = 'id_barang';

    protected $allowedFields = [
        'kode_barang',
        'nama_barang',
        'id_kategori',
        'id_satuan',
        'merek',
        'model_tipe',
        'spesifikasi',
        'foto_barang',
        'deskripsi',
        'status',
        'created_by',
        'updated_by',
    ];
}
