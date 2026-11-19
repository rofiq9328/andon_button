<?php

namespace App\Models\Pendataan;

class KategoriBarangModel extends PendataanModel
{
    protected $table      = 'kategori_barang';
    protected $primaryKey = 'id_kategori';

    protected $allowedFields = [
        'kode_kategori',
        'nama_kategori',
        'deskripsi',
        'status',
    ];
}
