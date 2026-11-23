<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Libraries\Roles;

class TransaksiController extends BaseController
{
    private const JENIS_TRANSAKSI = [
        'SALDO_AWAL'  => 'Saldo Awal',
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

    private const KONDISI_BARANG = [
        'BAIK',
        'RUSAK',
        'DALAM_PERBAIKAN',
        'TIDAK_LAYAK',
    ];

    private function canCreate(): bool
    {
        return Roles::hasAny(['admin', 'petugas_area']);
    }

    private function createOnly()
    {
        if (!$this->canCreate()) {
            session()->setFlashdata('error', 'Anda tidak memiliki hak untuk membuat transaksi.');
            return redirect()->to('pendataan/transaksi');
        }

        return null;
    }

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
            if (isset($ringkasan[$count['status_transaksi']])) {
                $ringkasan[$count['status_transaksi']] = (int) $count['total'];
            }
        }

        return view('pendataan/transaksi/index', [
            'title'          => 'Transaksi Barang',
            'transaksi'      => $transaksi,
            'ringkasan'      => $ringkasan,
            'jenisTransaksi' => $availableTypes,
            'statusTransaksi'=> self::STATUS_TRANSAKSI,
            'canCreate'      => $this->canCreate(),
            'filter'         => [
                'jenis'          => $jenis,
                'status'         => $status,
                'q'              => $keyword,
                'tanggal_mulai'  => $tanggalMulai,
                'tanggal_selesai'=> $tanggalSelesai,
            ],
        ]);
    }

    public function detail(int $id)
    {
        $db = db_connect();

        $transaksi = $db->table('transaksi_barang t')
            ->select([
                't.*',
                'p.full_name AS nama_pembuat',
                'a.full_name AS nama_penyetuju',
                'pr.full_name AS nama_pemroses',
                'po.full_name AS nama_pemosting',
            ])
            ->join('users p', 'p.id = t.dibuat_oleh', 'left')
            ->join('users a', 'a.id = t.disetujui_oleh', 'left')
            ->join('users pr', 'pr.id = t.diproses_oleh', 'left')
            ->join('users po', 'po.id = t.diposting_oleh', 'left')
            ->where('t.id_transaksi', $id)
            ->get()
            ->getRowArray();

        if (!$transaksi) {
            return redirect()->to('pendataan/transaksi')
                ->with('error', 'Data transaksi tidak ditemukan.');
        }

        $detail = $db->table('detail_transaksi_barang d')
            ->select([
                'd.id_detail',
                'd.jumlah',
                'd.keterangan AS keterangan_detail',
                'd.kondisi_asal',
                'd.kondisi_tujuan',
                'b.kode_barang',
                'b.nama_barang',
                's.kode_satuan',
                's.nama_satuan',
                'a_asal.kode_area AS kode_area_asal',
                'a_asal.nama_area AS nama_area_asal',
                'a_tujuan.kode_area AS kode_area_tujuan',
                'a_tujuan.nama_area AS nama_area_tujuan',
            ])
            ->join('barang b', 'b.id_barang = d.id_barang')
            ->join('satuan s', 's.id_satuan = b.id_satuan')
            ->join('area a_asal', 'a_asal.id_area = d.id_area_asal', 'left')
            ->join('area a_tujuan', 'a_tujuan.id_area = d.id_area_tujuan', 'left')
            ->where('d.id_transaksi', $id)
            ->orderBy('d.id_detail', 'ASC')
            ->get()
            ->getResultArray();

        $currentUserId = (int) (session()->get('user_id') ?? 0);
        $isDirectStockType = in_array($transaksi['jenis_transaksi'], ['SALDO_AWAL', 'TRANSFER'], true);
        $canSubmit = !$isDirectStockType
            && $transaksi['status_transaksi'] === 'DRAFT'
            && (Roles::has('admin') || (Roles::has('petugas_area') && (int) $transaksi['dibuat_oleh'] === $currentUserId));
        $canVerify = !$isDirectStockType
            && $transaksi['status_transaksi'] === 'DIAJUKAN'
            && Roles::has('verifikator');

        return view('pendataan/transaksi/detail', [
            'title'     => 'Detail Transaksi',
            'transaksi' => $transaksi,
            'detail'    => $detail,
            'canSubmit' => $canSubmit,
            'canVerify' => $canVerify,
        ]);
    }

    public function verifikasi()
    {
        if (!Roles::has('verifikator')) {
            return redirect()->to('pendataan/transaksi')->with('error', 'Halaman ini khusus Verifikator.');
        }

        $rows = db_connect()->table('transaksi_barang t')
            ->select('t.id_transaksi, t.nomor_transaksi, t.jenis_transaksi, t.tanggal_transaksi, t.keterangan, u.full_name AS nama_pembuat')
            ->join('users u', 'u.id = t.dibuat_oleh', 'left')
            ->where('t.status_transaksi', 'DIAJUKAN')
            ->whereNotIn('t.jenis_transaksi', ['SALDO_AWAL', 'TRANSFER'])
            ->orderBy('t.tanggal_transaksi', 'ASC')
            ->orderBy('t.id_transaksi', 'ASC')
            ->get()->getResultArray();

        return view('pendataan/transaksi/verifikasi', [
            'title' => 'Verifikasi Transaksi',
            'transaksi' => $rows,
        ]);
    }

    public function setujui(int $id)
    {
        if (!Roles::has('verifikator')) {
            return redirect()->to('pendataan/transaksi')->with('error', 'Anda tidak memiliki hak verifikasi.');
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        if ($userId < 1) {
            return redirect()->to('pendataan/transaksi')->with('error', 'Sesi pengguna tidak valid.');
        }

        $db = db_connect();
        $updated = $db->table('transaksi_barang')
            ->where('id_transaksi', $id)
            ->where('status_transaksi', 'DIAJUKAN')
            ->whereNotIn('jenis_transaksi', ['SALDO_AWAL', 'TRANSFER'])
            ->update([
                'status_transaksi' => 'DISETUJUI',
                'disetujui_oleh' => $userId,
                'alasan_penolakan' => null,
            ]);

        if (!$updated || !$db->affectedRows()) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Transaksi tidak dapat disetujui. Pastikan statusnya masih Diajukan.');
        }

        return redirect()->to('pendataan/transaksi/detail/' . $id)
            ->with('success', 'Transaksi berhasil disetujui. Saldo barang belum berubah.');
    }

    public function tolak(int $id)
    {
        if (!Roles::has('verifikator')) {
            return redirect()->to('pendataan/transaksi')->with('error', 'Anda tidak memiliki hak verifikasi.');
        }

        $alasan = trim((string) $this->request->getPost('alasan_penolakan'));
        if ($alasan === '' || strlen($alasan) > 5000) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Alasan penolakan wajib diisi dan maksimal 5.000 karakter.');
        }

        $db = db_connect();
        $updated = $db->table('transaksi_barang')
            ->where('id_transaksi', $id)
            ->where('status_transaksi', 'DIAJUKAN')
            ->whereNotIn('jenis_transaksi', ['SALDO_AWAL', 'TRANSFER'])
            ->update([
                'status_transaksi' => 'DITOLAK',
                'alasan_penolakan' => $alasan,
            ]);

        if (!$updated || !$db->affectedRows()) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Transaksi tidak dapat ditolak. Pastikan statusnya masih Diajukan.');
        }

        return redirect()->to('pendataan/transaksi/detail/' . $id)
            ->with('success', 'Transaksi berhasil ditolak dan alasan penolakan tersimpan. Saldo barang belum berubah.');
    }

    public function submit(int $id)
    {
        $db = db_connect();
        $currentUserId = (int) (session()->get('user_id') ?? 0);

        if ($currentUserId < 1 || !Roles::hasAny(['admin', 'petugas_area'])) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Anda tidak memiliki hak untuk mengajukan transaksi.');
        }

        $transaction = $db->table('transaksi_barang')
            ->where('id_transaksi', $id)
            ->get()
            ->getRowArray();

        if (!$transaction) {
            return redirect()->to('pendataan/transaksi')->with('error', 'Data transaksi tidak ditemukan.');
        }

        if ($transaction['status_transaksi'] !== 'DRAFT') {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Hanya transaksi berstatus Draft yang dapat diajukan.');
        }

        if (in_array($transaction['jenis_transaksi'], ['SALDO_AWAL', 'TRANSFER'], true)) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Saldo Awal dan Pindah Area menggunakan pencatatan langsung oleh Administrator, bukan alur pengajuan.');
        }

        if (!Roles::has('admin') && (int) $transaction['dibuat_oleh'] !== $currentUserId) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Petugas Area hanya dapat mengajukan Draft yang dibuat sendiri.');
        }

        $detailCount = $db->table('detail_transaksi_barang')
            ->where('id_transaksi', $id)
            ->countAllResults();

        if ($detailCount < 1) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Transaksi tidak memiliki rincian barang dan tidak dapat diajukan.');
        }

        $updated = $db->table('transaksi_barang')
            ->where('id_transaksi', $id)
            ->where('status_transaksi', 'DRAFT')
            ->update(['status_transaksi' => 'DIAJUKAN']);

        if (!$updated || !$db->affectedRows()) {
            return redirect()->to('pendataan/transaksi/detail/' . $id)
                ->with('error', 'Pengajuan transaksi gagal. Silakan muat ulang halaman dan coba kembali.');
        }

        return redirect()->to('pendataan/transaksi/detail/' . $id)
            ->with('success', 'Transaksi berhasil diajukan dan menunggu verifikasi. Saldo barang belum berubah.');
    }

    public function create()
    {
        if ($response = $this->createOnly()) {
            return $response;
        }

        $jenis = strtoupper(trim((string) $this->request->getGet('jenis')));
        $availableTypes = self::JENIS_TRANSAKSI;
        if (!Roles::has('admin')) {
            unset($availableTypes['SALDO_AWAL'], $availableTypes['TRANSFER']);
        }
        if (!isset($availableTypes[$jenis])) {
            $jenis = 'PENERIMAAN';
        }

        $db = db_connect();
        $barang = $db->table('barang')
            ->select('id_barang, kode_barang, nama_barang')
            ->where('status', 1)
            ->orderBy('nama_barang', 'ASC')
            ->get()->getResultArray();

        $areas = $db->table('area')
            ->select('id_area, kode_area, nama_area, cakupan_area')
            ->where('status', 1)
            ->orderBy('nama_area', 'ASC')
            ->get()->getResultArray();

        return view('pendataan/transaksi/create', [
            'title'          => 'Buat Transaksi Barang',
            'jenisTransaksi' => $availableTypes,
            'kondisiBarang'  => self::KONDISI_BARANG,
            'barang'         => $barang,
            'areas'          => $areas,
            'jenisDefault'   => $jenis,
        ]);
    }

    public function store()
    {
        if ($response = $this->createOnly()) {
            return $response;
        }

        $jenis = strtoupper(trim((string) $this->request->getPost('jenis_transaksi')));
        $tanggalRaw = trim((string) $this->request->getPost('tanggal_transaksi'));
        $areaAsalRaw = trim((string) $this->request->getPost('id_area_asal'));
        $areaTujuanRaw = trim((string) $this->request->getPost('id_area_tujuan'));
        $kondisi = strtoupper(trim((string) $this->request->getPost('kondisi')));
        $keterangan = trim((string) $this->request->getPost('keterangan'));
        $barangIds = $this->request->getPost('id_barang');
        $jumlahs = $this->request->getPost('jumlah');
        $errors = [];

        if (!isset(self::JENIS_TRANSAKSI[$jenis])) {
            $errors[] = 'Jenis transaksi tidak valid.';
        }
        if (in_array($jenis, ['SALDO_AWAL', 'TRANSFER'], true) && !Roles::has('admin')) {
            $errors[] = 'Saldo Awal dan Pindah Area hanya dapat dilakukan oleh Administrator.';
        }

        $tanggal = \DateTime::createFromFormat('!Y-m-d\TH:i', $tanggalRaw);
        if (!$tanggal || $tanggal->format('Y-m-d\TH:i') !== $tanggalRaw) {
            $errors[] = 'Tanggal transaksi wajib diisi dengan format yang benar.';
        }

        $areaAsal = null;
        $areaTujuan = null;
        if ($jenis === 'PENGELUARAN' || $jenis === 'TRANSFER') {
            if ($areaAsalRaw === '' || !ctype_digit($areaAsalRaw) || (int) $areaAsalRaw < 1) {
                $errors[] = 'Area asal wajib dipilih.';
            } else {
                $areaAsal = (int) $areaAsalRaw;
            }
        }
        if (in_array($jenis, ['SALDO_AWAL', 'PENERIMAAN', 'TRANSFER'], true)) {
            if ($areaTujuanRaw === '' || !ctype_digit($areaTujuanRaw) || (int) $areaTujuanRaw < 1) {
                $errors[] = 'Area tujuan wajib dipilih.';
            } else {
                $areaTujuan = (int) $areaTujuanRaw;
            }
        }
        if ($areaAsal !== null && $areaAsal === $areaTujuan) {
            $errors[] = 'Area asal dan area tujuan tidak boleh sama.';
        }

        if (!in_array($kondisi, self::KONDISI_BARANG, true)) {
            $errors[] = 'Kondisi barang tidak valid.';
        }

        if (strlen($keterangan) > 5000) {
            $errors[] = 'Keterangan maksimal 5.000 karakter.';
        }

        if (!is_array($barangIds) || !is_array($jumlahs) || count($barangIds) !== count($jumlahs)) {
            $errors[] = 'Detail barang tidak lengkap.';
            $barangIds = [];
            $jumlahs = [];
        }

        $db = db_connect();
        $detailRows = [];
        $activeBarangIds = [];
        $activeAreas = [];

        if ($areaAsal !== null) {
            $activeAreas[] = $areaAsal;
        }
        if ($areaTujuan !== null) {
            $activeAreas[] = $areaTujuan;
        }
        if ($activeAreas !== []) {
            $foundAreas = $db->table('area')
                ->select('id_area')
                ->whereIn('id_area', array_values(array_unique($activeAreas)))
                ->where('status', 1)
                ->get()->getResultArray();
            $foundAreaIds = array_map('intval', array_column($foundAreas, 'id_area'));
            foreach (array_unique($activeAreas) as $areaId) {
                if (!in_array($areaId, $foundAreaIds, true)) {
                    $errors[] = 'Area yang dipilih tidak ditemukan atau sudah nonaktif.';
                    break;
                }
            }
        }

        foreach ($barangIds as $index => $barangRaw) {
            $barangRaw = trim((string) $barangRaw);
            $jumlahRaw = trim((string) ($jumlahs[$index] ?? ''));

            if ($barangRaw === '' && $jumlahRaw === '') {
                continue;
            }

            if ($barangRaw === '' || !ctype_digit($barangRaw) || (int) $barangRaw < 1) {
                $errors[] = 'Pilih barang pada setiap baris detail.';
                continue;
            }

            $barangId = (int) $barangRaw;
            if (!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/', $jumlahRaw) || (float) $jumlahRaw <= 0) {
                $errors[] = 'Jumlah barang harus berupa angka lebih dari nol dan maksimal dua angka desimal.';
                continue;
            }
            $jumlah = (float) $jumlahRaw;

            $activeBarangIds[] = $barangId;
            $detailRows[] = [
                'id_barang'       => $barangId,
                'id_area_asal'    => $areaAsal,
                'id_area_tujuan'  => $areaTujuan,
                'kondisi_asal'    => in_array($jenis, ['PENGELUARAN', 'TRANSFER'], true) ? $kondisi : null,
                'kondisi_tujuan'  => in_array($jenis, ['SALDO_AWAL', 'PENERIMAAN', 'TRANSFER'], true) ? $kondisi : null,
                'jumlah'          => number_format((float) $jumlah, 2, '.', ''),
                'keterangan'      => null,
            ];
        }

        if ($detailRows === []) {
            $errors[] = 'Tambahkan minimal satu barang pada detail transaksi.';
        }

        if ($activeBarangIds !== []) {
            $foundBarang = $db->table('barang')
                ->select('id_barang')
                ->whereIn('id_barang', array_values(array_unique($activeBarangIds)))
                ->where('status', 1)
                ->get()->getResultArray();
            $foundBarangIds = array_map('intval', array_column($foundBarang, 'id_barang'));
            foreach (array_unique($activeBarangIds) as $barangId) {
                if (!in_array($barangId, $foundBarangIds, true)) {
                    $errors[] = 'Salah satu barang tidak ditemukan atau sudah nonaktif.';
                    break;
                }
            }
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        if ($userId < 1) {
            $errors[] = 'Sesi pengguna tidak valid. Silakan login kembali.';
        }

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', array_values(array_unique($errors)));
        }

        $isDirectStock = in_array($jenis, ['SALDO_AWAL', 'TRANSFER'], true);
        $nomor = $this->generateTransactionNumber($db, $tanggal->format('YmdHis'));
        $db->transBegin();

        // Lock saldo sumber sebelum Pindah Area agar stok tidak dipindahkan
        // melebihi jumlah yang benar-benar tersedia.
        $transferNeeds = [];
        if ($jenis === 'TRANSFER') {
            foreach ($detailRows as $row) {
                $key = (int) $row['id_barang'] . ':' . $row['kondisi_asal'];
                if (!isset($transferNeeds[$key])) {
                    $transferNeeds[$key] = [
                        'id_barang' => (int) $row['id_barang'],
                        'kondisi' => $row['kondisi_asal'],
                        'jumlah' => 0.0,
                    ];
                }
                $transferNeeds[$key]['jumlah'] += (float) $row['jumlah'];
            }
            ksort($transferNeeds, SORT_STRING);

            foreach ($transferNeeds as $need) {
                $sourceBalance = $db->query(
                    'SELECT jumlah FROM saldo_barang_area WHERE id_area = ? AND id_barang = ? AND kondisi = ? FOR UPDATE',
                    [$areaAsal, $need['id_barang'], $need['kondisi']]
                )->getRowArray();

                $available = $sourceBalance ? (float) $sourceBalance['jumlah'] : 0.0;
                if ($available + 0.00001 < $need['jumlah']) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('errors', [
                        'Saldo ' . $need['kondisi'] . ' untuk barang ID ' . $need['id_barang']
                        . ' di area asal tidak mencukupi. Tersedia ' . number_format($available, 2, ',', '.')
                        . ', dibutuhkan ' . number_format($need['jumlah'], 2, ',', '.') . '.',
                    ]);
                }
            }
        }

        $db->table('transaksi_barang')->insert([
            'nomor_transaksi'  => $nomor,
            'jenis_transaksi'  => $jenis,
            'alur_persetujuan' => $isDirectStock ? 'LANGSUNG' : 'APPROVAL',
            'tanggal_transaksi'=> $tanggal->format('Y-m-d H:i:s'),
            'dibuat_oleh'      => $userId,
            'diproses_oleh'    => $isDirectStock ? $userId : null,
            'diposting_oleh'   => $isDirectStock ? $userId : null,
            'status_transaksi' => $isDirectStock ? 'DIPOSTING' : 'DRAFT',
            'keterangan'       => $keterangan === '' ? null : $keterangan,
        ]);

        $idTransaksi = (int) $db->insertID();
        if ($idTransaksi > 0) {
            foreach ($detailRows as $row) {
                $row['id_transaksi'] = $idTransaksi;
                $db->table('detail_transaksi_barang')->insert($row);
            }
        }

        // Saldo Awal menambahkan stok; Pindah Area mengurangi sumber dan
        // menambahkan tujuan menggunakan kondisi barang yang sama.
        if ($idTransaksi > 0 && $isDirectStock) {
            if ($jenis === 'TRANSFER') {
                foreach ($transferNeeds as $need) {
                    $updated = $db->query(
                        'UPDATE saldo_barang_area
                         SET jumlah = jumlah - ?, updated_at = CURRENT_TIMESTAMP
                         WHERE id_area = ? AND id_barang = ? AND kondisi = ? AND jumlah >= ?',
                        [$need['jumlah'], $areaAsal, $need['id_barang'], $need['kondisi'], $need['jumlah']]
                    );
                    if (!$updated || $db->affectedRows() !== 1) {
                        $db->transRollback();
                        return redirect()->back()->withInput()->with('errors', [
                            'Saldo area asal berubah saat proses berlangsung. Pindah Area dibatalkan; silakan periksa saldo lalu ulangi.',
                        ]);
                    }
                }
            }

            foreach ($detailRows as $row) {
                $db->query(
                    'INSERT INTO saldo_barang_area (id_area, id_barang, kondisi, jumlah, updated_at)
                     VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
                     ON DUPLICATE KEY UPDATE jumlah = jumlah + VALUES(jumlah), updated_at = CURRENT_TIMESTAMP',
                    [$areaTujuan, $row['id_barang'], $row['kondisi_tujuan'], $row['jumlah']]
                );
            }
        }

        if ($idTransaksi < 1 || !$db->transStatus()) {
            $db->transRollback();
            return redirect()->back()->withInput()
                ->with('errors', ['Transaksi gagal disimpan. Tidak ada perubahan saldo barang.']);
        }

        $db->transCommit();

        if ($jenis === 'TRANSFER') {
            $message = 'Barang berhasil dipindahkan dan saldo Area asal/tujuan langsung diperbarui. Nomor transaksi: ' . $nomor . '.';
        } elseif ($jenis === 'SALDO_AWAL') {
            $message = 'Saldo Awal berhasil dicatat dan saldo Area langsung diperbarui. Nomor transaksi: ' . $nomor . '.';
        } else {
            $message = 'Transaksi Draft berhasil disimpan dengan nomor ' . $nomor . '. Saldo barang belum berubah.';
        }

        return redirect()->to('pendataan/transaksi')
            ->with('success', $message);
    }

    private function generateTransactionNumber($db, string $datePart): string
    {
        do {
            $number = 'TRX-' . $datePart . '-' . strtoupper(bin2hex(random_bytes(3)));
            $exists = $db->table('transaksi_barang')
                ->where('nomor_transaksi', $number)
                ->countAllResults() > 0;
        } while ($exists);

        return $number;
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
