<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;

class TransaksiController extends BaseController
{
    private const JENIS_TRANSAKSI = [
        'PENERIMAAN'  => 'Barang Masuk',
        'PENGELUARAN' => 'Barang Keluar',
        'TRANSFER'    => 'Pindah Area',
    ];

    private const STATUS_TRANSAKSI = [
        'DRAFT',
        'DIAJUKAN',
        'DISETUJUI',
        'DITOLAK',
        'DIPOSTING',
        'DIBATALKAN',
    ];

    public function index()
    {
        $db = db_connect();

        $jenis  = strtoupper(trim((string) $this->request->getGet('jenis')));
        $status = strtoupper(trim((string) $this->request->getGet('status')));
        $keyword = trim((string) $this->request->getGet('q'));
        $tanggalMulai = trim((string) $this->request->getGet('tanggal_mulai'));
        $tanggalSelesai = trim((string) $this->request->getGet('tanggal_selesai'));

        $builder = $db->table('transaksi_barang t')
            ->select([
                't.id_transaksi',
                't.nomor_transaksi',
                't.jenis_transaksi',
                't.alur_persetujuan',
                't.tanggal_transaksi',
                't.status_transaksi',
                't.keterangan',
                'u.full_name AS nama_pembuat',
            ])
            ->join('users u', 'u.id = t.dibuat_oleh', 'left');

        if (isset(self::JENIS_TRANSAKSI[$jenis])) {
            $builder->where('t.jenis_transaksi', $jenis);
        }

        if (in_array($status, self::STATUS_TRANSAKSI, true)) {
            $builder->where('t.status_transaksi', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('t.nomor_transaksi', $keyword)
                ->orLike('u.full_name', $keyword)
                ->orLike('t.keterangan', $keyword)
                ->groupEnd();
        }

        if ($this->isValidDate($tanggalMulai)) {
            $builder->where('t.tanggal_transaksi >=', $tanggalMulai . ' 00:00:00');
        }

        if ($this->isValidDate($tanggalSelesai)) {
            $builder->where('t.tanggal_transaksi <=', $tanggalSelesai . ' 23:59:59');
        }

        $transaksi = $builder
            ->orderBy('t.tanggal_transaksi', 'DESC')
            ->orderBy('t.id_transaksi', 'DESC')
            ->get()
            ->getResultArray();

        $statusCounts = $db->table('transaksi_barang')
            ->select('status_transaksi, COUNT(*) AS total', false)
            ->groupBy('status_transaksi')
            ->get()
            ->getResultArray();

        $ringkasan = array_fill_keys(self::STATUS_TRANSAKSI, 0);
        foreach ($statusCounts as $count) {
            $ringkasan[$count['status_transaksi']] = (int) $count['total'];
        }

        return view('pendataan/transaksi/index', [
            'title'          => 'Transaksi Barang',
            'transaksi'      => $transaksi,
            'ringkasan'      => $ringkasan,
            'jenisTransaksi' => self::JENIS_TRANSAKSI,
            'statusTransaksi'=> self::STATUS_TRANSAKSI,
            'filter'         => [
                'jenis'          => $jenis,
                'status'         => $status,
                'q'              => $keyword,
                'tanggal_mulai'  => $tanggalMulai,
                'tanggal_selesai'=> $tanggalSelesai,
            ],
        ]);
    }

    private function isValidDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
