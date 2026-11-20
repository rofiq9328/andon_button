<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;

class SaldoBarangController extends BaseController
{
    private const KONDISI = [
        'BAIK' => 'Baik',
        'RUSAK' => 'Rusak',
        'DALAM_PERBAIKAN' => 'Dalam Perbaikan',
        'TIDAK_LAYAK' => 'Tidak Layak',
    ];

    public function index()
    {
        $db = db_connect();
        $today = date('Y-m-d');

        $areaFilter = trim((string) $this->request->getGet('area'));
        $fungsiFilter = strtoupper(trim((string) $this->request->getGet('fungsi')));
        $kategoriFilter = trim((string) $this->request->getGet('kategori'));
        $statusFilter = strtoupper(trim((string) $this->request->getGet('status')));
        $keyword = trim((string) $this->request->getGet('q'));

        if ($areaFilter !== '' && (!ctype_digit($areaFilter) || (int) $areaFilter < 1)) {
            $areaFilter = '';
        }
        if ($kategoriFilter !== '' && (!ctype_digit($kategoriFilter) || (int) $kategoriFilter < 1)) {
            $kategoriFilter = '';
        }
        if (!in_array($fungsiFilter, ['PENGGUNA', 'PENYIMPANAN', 'KEDUANYA'], true)) {
            $fungsiFilter = '';
        }
        if (!in_array($statusFilter, ['KURANG', 'SESUAI', 'LEBIH', 'TANPA_STANDAR'], true)) {
            $statusFilter = '';
        }

        $applyFilters = function ($builder) use ($areaFilter, $fungsiFilter, $kategoriFilter, $keyword) {
            if ($areaFilter !== '') {
                $builder->where('a.id_area', (int) $areaFilter);
            }
            if ($fungsiFilter !== '') {
                $builder->where('a.fungsi_stok', $fungsiFilter);
            }
            if ($kategoriFilter !== '') {
                $builder->where('b.id_kategori', (int) $kategoriFilter);
            }
            if ($keyword !== '') {
                $builder->groupStart()
                    ->like('a.kode_area', $keyword)
                    ->orLike('a.nama_area', $keyword)
                    ->orLike('b.kode_barang', $keyword)
                    ->orLike('b.nama_barang', $keyword)
                    ->orLike('k.nama_kategori', $keyword)
                    ->orLike('f.nama_factory', $keyword)
                    ->groupEnd();
            }

            return $builder;
        };

        // Saldo aktual diringkas per kombinasi Area dan Barang, tetapi tetap
        // dipisahkan per kondisi agar stok tidak layak pakai tidak tersembunyi.
        $stockQuery = $db->table('saldo_barang_area s')
            ->select([
                'a.id_area',
                'a.kode_area',
                'a.nama_area',
                'a.jenis_area',
                'a.fungsi_stok',
                'a.status AS status_area',
                'f.kode_factory',
                'f.nama_factory',
                'b.id_barang',
                'b.kode_barang',
                'b.nama_barang',
                'b.status AS status_barang',
                'k.nama_kategori',
                'u.kode_satuan',
                'u.nama_satuan',
            ])
            ->select("SUM(CASE WHEN s.kondisi = 'BAIK' THEN s.jumlah ELSE 0 END) AS stok_baik", false)
            ->select("SUM(CASE WHEN s.kondisi = 'RUSAK' THEN s.jumlah ELSE 0 END) AS stok_rusak", false)
            ->select("SUM(CASE WHEN s.kondisi = 'DALAM_PERBAIKAN' THEN s.jumlah ELSE 0 END) AS stok_perbaikan", false)
            ->select("SUM(CASE WHEN s.kondisi = 'TIDAK_LAYAK' THEN s.jumlah ELSE 0 END) AS stok_tidak_layak", false)
            ->select('MAX(s.updated_at) AS saldo_updated_at', false)
            ->join('area a', 'a.id_area = s.id_area')
            ->join('barang b', 'b.id_barang = s.id_barang')
            ->join('kategori_barang k', 'k.id_kategori = b.id_kategori', 'left')
            ->join('satuan u', 'u.id_satuan = b.id_satuan', 'left')
            ->join('factory f', 'f.id_factory = a.id_factory', 'left');

        $applyFilters($stockQuery);
        $stockRows = $stockQuery
            ->groupBy([
                'a.id_area', 'a.kode_area', 'a.nama_area', 'a.jenis_area',
                'a.fungsi_stok', 'a.status', 'f.kode_factory', 'f.nama_factory',
                'b.id_barang', 'b.kode_barang', 'b.nama_barang', 'b.status',
                'k.nama_kategori', 'u.kode_satuan', 'u.nama_satuan',
            ])
            ->get()
            ->getResultArray();

        // Standar yang dibandingkan hanyalah yang aktif dan berlaku pada hari ini.
        $standardQuery = $db->table('standar_barang_area s')
            ->select([
                'a.id_area',
                'a.kode_area',
                'a.nama_area',
                'a.jenis_area',
                'a.fungsi_stok',
                'a.status AS status_area',
                'f.kode_factory',
                'f.nama_factory',
                'b.id_barang',
                'b.kode_barang',
                'b.nama_barang',
                'b.status AS status_barang',
                'k.nama_kategori',
                'u.kode_satuan',
                'u.nama_satuan',
                's.jumlah_standar',
            ])
            ->join('area a', 'a.id_area = s.id_area')
            ->join('barang b', 'b.id_barang = s.id_barang')
            ->join('kategori_barang k', 'k.id_kategori = b.id_kategori', 'left')
            ->join('satuan u', 'u.id_satuan = b.id_satuan', 'left')
            ->join('factory f', 'f.id_factory = a.id_factory', 'left')
            ->where('s.status', 1)
            ->where('s.berlaku_mulai <=', $today)
            ->groupStart()
                ->where('s.berlaku_sampai', null)
                ->orWhere('s.berlaku_sampai >=', $today)
            ->groupEnd();

        $applyFilters($standardQuery);
        $standardRows = $standardQuery
            ->orderBy('a.nama_area', 'ASC')
            ->orderBy('b.nama_barang', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($stockRows as $row) {
            $key = $row['id_area'] . ':' . $row['id_barang'];
            $map[$key] = array_merge($row, [
                'jumlah_standar' => null,
                'stok_baik' => (float) $row['stok_baik'],
                'stok_rusak' => (float) $row['stok_rusak'],
                'stok_perbaikan' => (float) $row['stok_perbaikan'],
                'stok_tidak_layak' => (float) $row['stok_tidak_layak'],
            ]);
        }

        foreach ($standardRows as $row) {
            $key = $row['id_area'] . ':' . $row['id_barang'];
            if (!isset($map[$key])) {
                $map[$key] = array_merge($row, [
                    'jumlah_standar' => (float) $row['jumlah_standar'],
                    'stok_baik' => 0.0,
                    'stok_rusak' => 0.0,
                    'stok_perbaikan' => 0.0,
                    'stok_tidak_layak' => 0.0,
                    'saldo_updated_at' => null,
                ]);
            } else {
                $map[$key]['jumlah_standar'] = (float) $row['jumlah_standar'];
            }
        }

        $items = [];
        foreach ($map as $row) {
            $row['total_fisik'] = $row['stok_baik']
                + $row['stok_rusak']
                + $row['stok_perbaikan']
                + $row['stok_tidak_layak'];

            if ($row['jumlah_standar'] === null) {
                $row['selisih'] = null;
                $row['status_kebutuhan'] = 'TANPA_STANDAR';
            } else {
                // Kecukupan kebutuhan dihitung memakai stok berkondisi BAIK.
                $row['selisih'] = $row['stok_baik'] - $row['jumlah_standar'];
                if ($row['selisih'] < -0.00001) {
                    $row['status_kebutuhan'] = 'KURANG';
                } elseif ($row['selisih'] > 0.00001) {
                    $row['status_kebutuhan'] = 'LEBIH';
                } else {
                    $row['status_kebutuhan'] = 'SESUAI';
                }
            }

            if ($statusFilter !== '' && $row['status_kebutuhan'] !== $statusFilter) {
                continue;
            }

            $items[] = $row;
        }

        usort($items, static function ($a, $b) {
            $areaCompare = strcasecmp($a['nama_area'], $b['nama_area']);
            if ($areaCompare !== 0) {
                return $areaCompare;
            }

            return strcasecmp($a['nama_barang'], $b['nama_barang']);
        });

        $summary = [
            'baris_barang' => count($items),
            'kurang' => 0,
            'sesuai' => 0,
            'lebih' => 0,
            'tanpa_standar' => 0,
            'area' => count(array_unique(array_column($items, 'id_area'))),
        ];

        foreach ($items as $row) {
            if ($row['status_kebutuhan'] === 'KURANG') $summary['kurang']++;
            if ($row['status_kebutuhan'] === 'SESUAI') $summary['sesuai']++;
            if ($row['status_kebutuhan'] === 'LEBIH') $summary['lebih']++;
            if ($row['status_kebutuhan'] === 'TANPA_STANDAR') $summary['tanpa_standar']++;
        }

        $areas = $db->table('area a')
            ->select('a.id_area, a.kode_area, a.nama_area, a.fungsi_stok')
            ->orderBy('a.nama_area', 'ASC')
            ->get()->getResultArray();

        $categories = $db->table('kategori_barang')
            ->select('id_kategori, kode_kategori, nama_kategori')
            ->orderBy('nama_kategori', 'ASC')
            ->get()->getResultArray();

        return view('pendataan/saldo/index', [
            'title' => 'Saldo Barang',
            'items' => $items,
            'summary' => $summary,
            'areas' => $areas,
            'categories' => $categories,
            'filter' => [
                'area' => $areaFilter,
                'fungsi' => $fungsiFilter,
                'kategori' => $kategoriFilter,
                'status' => $statusFilter,
                'q' => $keyword,
            ],
        ]);
    }
}
