<?php

namespace App\Models\Pendataan;

class DetailTransaksiBarangModel extends PendataanModel
{
    protected $table      = 'detail_transaksi_barang';
    protected $primaryKey = 'id_detail';

    protected $allowedFields = [
        'id_transaksi',
        'id_barang',
        'id_area_asal',
        'id_area_tujuan',
        'kondisi_asal',
        'kondisi_tujuan',
        'jumlah',
        'keterangan',
    ];

    protected $useTimestamps = false;
}
