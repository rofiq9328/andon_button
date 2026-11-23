<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;

class LaporanBarangController extends BaseController
{
    private function isValidDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function getFilters(): array
    {
        $type = strtolower(trim((string) $this->request->getGet('jenis_laporan')));
        if (!in_array($type, ['saldo', 'transaksi', 'mutasi'], true)) {
            $type = 'saldo';
        }

        $area = trim((string) $this->request->getGet('area'));
        if ($area !== '' && (!ctype_digit($area) || (int) $area < 1)) {
            $area = '';
        }

        $category = trim((string) $this->request->getGet('kategori'));
        if ($category !== '' && (!ctype_digit($category) || (int) $category < 1)) {
            $category = '';
        }

        $start = trim((string) $this->request->getGet('tanggal_mulai'));
        $end = trim((string) $this->request->getGet('tanggal_selesai'));
        if (!$this->isValidDate($start)) {
            $start = '';
        }
        if (!$this->isValidDate($end)) {
            $end = '';
        }
        if ($start !== '' && $end !== '' && $end < $start) {
            $end = '';
        }

        return [
            'jenis_laporan' => $type,
            'area' => $area,
            'kategori' => $category,
            'q' => trim((string) $this->request->getGet('q')),
            'tanggal_mulai' => $start,
            'tanggal_selesai' => $end,
        ];
    }

    private function applyItemFilters($builder, array $filters)
    {
        if ($filters['area'] !== '') {
            $builder->where('a.id_area', (int) $filters['area']);
        }
        if ($filters['kategori'] !== '') {
            $builder->where('b.id_kategori', (int) $filters['kategori']);
        }
        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('a.kode_area', $filters['q'])
                ->orLike('a.nama_area', $filters['q'])
                ->orLike('b.kode_barang', $filters['q'])
                ->orLike('b.nama_barang', $filters['q'])
                ->groupEnd();
        }

        return $builder;
    }

    private function getReportData(array $filters): array
    {
        $db = db_connect();
        $type = $filters['jenis_laporan'];

        if ($type === 'saldo') {
            $builder = $db->table('saldo_barang_area s')
                ->select([
                    'a.id_area',
                    'a.kode_area',
                    'a.nama_area',
                    'a.fungsi_stok',
                    'f.nama_factory',
                    'b.id_barang',
                    'b.kode_barang',
                    'b.nama_barang',
                    'k.nama_kategori',
                    'u.kode_satuan',
                    'u.nama_satuan',
                ])
                ->select("SUM(CASE WHEN s.kondisi = 'BAIK' THEN s.jumlah ELSE 0 END) AS stok_baik", false)
                ->select("SUM(CASE WHEN s.kondisi = 'RUSAK' THEN s.jumlah ELSE 0 END) AS stok_rusak", false)
                ->select("SUM(CASE WHEN s.kondisi = 'DALAM_PERBAIKAN' THEN s.jumlah ELSE 0 END) AS stok_perbaikan", false)
                ->select("SUM(CASE WHEN s.kondisi = 'TIDAK_LAYAK' THEN s.jumlah ELSE 0 END) AS stok_tidak_layak", false)
                ->join('area a', 'a.id_area = s.id_area')
                ->join('barang b', 'b.id_barang = s.id_barang')
                ->join('kategori_barang k', 'k.id_kategori = b.id_kategori', 'left')
                ->join('satuan u', 'u.id_satuan = b.id_satuan', 'left')
                ->join('factory f', 'f.id_factory = a.id_factory', 'left');

            $this->applyItemFilters($builder, $filters);
            $rows = $builder
                ->groupBy([
                    'a.id_area', 'a.kode_area', 'a.nama_area', 'a.fungsi_stok',
                    'f.nama_factory', 'b.id_barang', 'b.kode_barang', 'b.nama_barang',
                    'k.nama_kategori', 'u.kode_satuan', 'u.nama_satuan',
                ])
                ->orderBy('a.nama_area', 'ASC')
                ->orderBy('b.nama_barang', 'ASC')
                ->get()
                ->getResultArray();

            $summary = [
                'baris' => count($rows),
                'area' => count(array_unique(array_column($rows, 'id_area'))),
                'total_fisik' => 0.0,
            ];
            foreach ($rows as $row) {
                $summary['total_fisik'] += (float) $row['stok_baik']
                    + (float) $row['stok_rusak']
                    + (float) $row['stok_perbaikan']
                    + (float) $row['stok_tidak_layak'];
            }

            return ['rows' => $rows, 'summary' => $summary];
        }

        $types = $type === 'mutasi'
            ? ['TRANSFER']
            : ['SALDO_AWAL', 'PENERIMAAN', 'PENGELUARAN'];

        $builder = $db->table('transaksi_barang t')
            ->select([
                't.id_transaksi',
                't.nomor_transaksi',
                't.tanggal_transaksi',
                't.jenis_transaksi',
                't.keterangan',
                'u.full_name AS nama_pencatat',
                'd.id_detail',
                'd.jumlah',
                'd.kondisi_asal',
                'd.kondisi_tujuan',
                'b.id_barang',
                'b.kode_barang',
                'b.nama_barang',
                'k.nama_kategori',
                's.kode_satuan',
                'a_asal.id_area AS id_area_asal',
                'a_asal.kode_area AS kode_area_asal',
                'a_asal.nama_area AS nama_area_asal',
                'a_tujuan.id_area AS id_area_tujuan',
                'a_tujuan.kode_area AS kode_area_tujuan',
                'a_tujuan.nama_area AS nama_area_tujuan',
            ])
            ->join('detail_transaksi_barang d', 'd.id_transaksi = t.id_transaksi')
            ->join('barang b', 'b.id_barang = d.id_barang')
            ->join('users u', 'u.id = t.dibuat_oleh', 'left')
            ->join('kategori_barang k', 'k.id_kategori = b.id_kategori', 'left')
            ->join('satuan s', 's.id_satuan = b.id_satuan', 'left')
            ->join('area a_asal', 'a_asal.id_area = d.id_area_asal', 'left')
            ->join('area a_tujuan', 'a_tujuan.id_area = d.id_area_tujuan', 'left')
            ->where('t.status_transaksi', 'DIPOSTING')
            ->whereIn('t.jenis_transaksi', $types);

        if ($filters['kategori'] !== '') {
            $builder->where('b.id_kategori', (int) $filters['kategori']);
        }
        if ($filters['area'] !== '') {
            $areaId = (int) $filters['area'];
            $builder->groupStart()
                ->where('d.id_area_asal', $areaId)
                ->orWhere('d.id_area_tujuan', $areaId)
                ->groupEnd();
        }
        if ($filters['tanggal_mulai'] !== '') {
            $builder->where('t.tanggal_transaksi >=', $filters['tanggal_mulai'] . ' 00:00:00');
        }
        if ($filters['tanggal_selesai'] !== '') {
            $builder->where('t.tanggal_transaksi <=', $filters['tanggal_selesai'] . ' 23:59:59');
        }
        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('t.nomor_transaksi', $filters['q'])
                ->orLike('b.kode_barang', $filters['q'])
                ->orLike('b.nama_barang', $filters['q'])
                ->orLike('a_asal.kode_area', $filters['q'])
                ->orLike('a_asal.nama_area', $filters['q'])
                ->orLike('a_tujuan.kode_area', $filters['q'])
                ->orLike('a_tujuan.nama_area', $filters['q'])
                ->orLike('u.full_name', $filters['q'])
                ->groupEnd();
        }

        $rows = $builder
            ->orderBy('t.tanggal_transaksi', 'DESC')
            ->orderBy('t.id_transaksi', 'DESC')
            ->orderBy('d.id_detail', 'ASC')
            ->get()
            ->getResultArray();

        $transactionIds = [];
        $totalQty = 0.0;
        foreach ($rows as $row) {
            $transactionIds[(int) $row['id_transaksi']] = true;
            $totalQty += (float) $row['jumlah'];
        }

        return [
            'rows' => $rows,
            'summary' => [
                'transaksi' => count($transactionIds),
                'baris' => count($rows),
                'total_qty' => $totalQty,
            ],
        ];
    }

    public function index()
    {
        $filters = $this->getFilters();
        $data = $this->getReportData($filters);
        $db = db_connect();

        $areas = $db->table('area')
            ->select('id_area, kode_area, nama_area, fungsi_stok')
            ->where('status', 1)
            ->orderBy('nama_area', 'ASC')
            ->get()
            ->getResultArray();

        $categories = $db->table('kategori_barang')
            ->select('id_kategori, nama_kategori')
            ->where('status', 1)
            ->orderBy('nama_kategori', 'ASC')
            ->get()
            ->getResultArray();

        return view('pendataan/laporan/index', [
            'title' => 'Laporan Barang',
            'jenisLaporan' => $filters['jenis_laporan'],
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'areas' => $areas,
            'categories' => $categories,
            'filter' => $filters,
        ]);
    }

    public function export()
    {
        $filters = $this->getFilters();
        $data = $this->getReportData($filters);
        $type = $filters['jenis_laporan'];

        $headings = $type === 'saldo'
            ? ['KODE AREA', 'AREA', 'FUNGSI STOK', 'FACTORY', 'KODE BARANG', 'NAMA BARANG', 'KATEGORI', 'SATUAN', 'STOK BAIK', 'STOK RUSAK', 'DALAM PERBAIKAN', 'TIDAK LAYAK', 'TOTAL FISIK']
            : ['TANGGAL', 'NOMOR TRANSAKSI', 'JENIS TRANSAKSI', 'KODE BARANG', 'NAMA BARANG', 'KATEGORI', 'JUMLAH', 'SATUAN', 'AREA ASAL', 'AREA TUJUAN', 'KONDISI', 'DICATAT OLEH', 'KETERANGAN'];

        $csv = "\xEF\xBB\xBF" . $this->csvLine($headings);

        foreach ($data['rows'] as $row) {
            if ($type === 'saldo') {
                $values = [
                    $row['kode_area'], $row['nama_area'], $row['fungsi_stok'], $row['nama_factory'],
                    $row['kode_barang'], $row['nama_barang'], $row['nama_kategori'], $row['kode_satuan'],
                    $row['stok_baik'], $row['stok_rusak'], $row['stok_perbaikan'], $row['stok_tidak_layak'],
                    (float) $row['stok_baik'] + (float) $row['stok_rusak'] + (float) $row['stok_perbaikan'] + (float) $row['stok_tidak_layak'],
                ];
            } else {
                $values = [
                    $row['tanggal_transaksi'], $row['nomor_transaksi'], $row['jenis_transaksi'],
                    $row['kode_barang'], $row['nama_barang'], $row['nama_kategori'], $row['jumlah'],
                    $row['kode_satuan'],
                    trim(($row['kode_area_asal'] ?? '') . ' ' . ($row['nama_area_asal'] ?? '')),
                    trim(($row['kode_area_tujuan'] ?? '') . ' ' . ($row['nama_area_tujuan'] ?? '')),
                    $row['kondisi_tujuan'] ?: $row['kondisi_asal'],
                    $row['nama_pencatat'], $row['keterangan'],
                ];
            }
            $csv .= $this->csvLine($values);
        }

        $filename = 'laporan_' . $type . '_' . date('Ymd_His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    private function csvLine(array $values): string
    {
        $escaped = [];
        foreach ($values as $value) {
            $text = (string) ($value ?? '');
            $escaped[] = '"' . str_replace('"', '""', $text) . '"';
        }

        return implode(';', $escaped) . "\r\n";
    }
}
