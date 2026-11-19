<?php

namespace App\Models\Pendataan;

class TransaksiBarangModel extends PendataanModel
{
    protected $table      = 'transaksi_barang';
    protected $primaryKey = 'id_transaksi';

    protected $allowedFields = [
        'nomor_transaksi',
        'jenis_transaksi',
        'alur_persetujuan',
        'tanggal_transaksi',
        'dibuat_oleh',
        'disetujui_oleh',
        'diproses_oleh',
        'diposting_oleh',
        'status_transaksi',
        'alasan_penolakan',
        'keterangan',
    ];
}
