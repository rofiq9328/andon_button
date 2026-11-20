<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Libraries\Roles;
use App\Models\Pendataan\AreaModel;
use App\Models\Pendataan\BarangModel;
use App\Models\Pendataan\StandarBarangAreaModel;

class StandarBarangAreaController extends BaseController
{
    private function adminOnly()
    {
        if (!Roles::has('admin')) {
            session()->setFlashdata('error', 'Hanya Admin yang dapat mengubah Kebutuhan Area.');
            return redirect()->to('pendataan/master/kebutuhan-area');
        }

        return null;
    }

    public function index()
    {
        $db = db_connect();
        $areaFilter = trim((string) $this->request->getGet('area'));
        $statusFilter = strtoupper(trim((string) $this->request->getGet('status')));
        $keyword = trim((string) $this->request->getGet('q'));

        $builder = $db->table('standar_barang_area s')
            ->select([
                's.*',
                'a.kode_area',
                'a.nama_area',
                'a.fungsi_stok',
                'b.kode_barang',
                'b.nama_barang',
                'u.kode_satuan',
                'u.nama_satuan',
            ])
            ->join('area a', 'a.id_area = s.id_area')
            ->join('barang b', 'b.id_barang = s.id_barang')
            ->join('satuan u', 'u.id_satuan = b.id_satuan');

        if ($areaFilter !== '' && ctype_digit($areaFilter) && (int) $areaFilter > 0) {
            $builder->where('s.id_area', (int) $areaFilter);
        }

        if ($statusFilter === 'AKTIF') {
            $builder->where('s.status', 1);
        } elseif ($statusFilter === 'NONAKTIF') {
            $builder->where('s.status', 0);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('a.kode_area', $keyword)
                ->orLike('a.nama_area', $keyword)
                ->orLike('b.kode_barang', $keyword)
                ->orLike('b.nama_barang', $keyword)
                ->orLike('s.keterangan', $keyword)
                ->groupEnd();
        }

        $items = $builder
            ->orderBy('a.nama_area', 'ASC')
            ->orderBy('b.nama_barang', 'ASC')
            ->orderBy('s.berlaku_mulai', 'DESC')
            ->get()
            ->getResultArray();

        $today = date('Y-m-d');
        $summaryQuery = $db->table('standar_barang_area')->where('status', 1);
        $activeCount = (clone $summaryQuery)->countAllResults();
        $currentCount = $db->table('standar_barang_area')
            ->where('status', 1)
            ->where('berlaku_mulai <=', $today)
            ->groupStart()
                ->where('berlaku_sampai', null)
                ->orWhere('berlaku_sampai >=', $today)
            ->groupEnd()
            ->countAllResults();

        $areas = (new AreaModel())
            ->where('status', 1)
            ->orderBy('nama_area', 'ASC')
            ->findAll();

        $barang = (new BarangModel())
            ->where('status', 1)
            ->orderBy('nama_barang', 'ASC')
            ->findAll();

        return view('pendataan/master/kebutuhan_area', [
            'title'        => 'Kebutuhan Area',
            'items'        => $items,
            'areas'        => $areas,
            'barang'       => $barang,
            'activeCount'  => $activeCount,
            'currentCount' => $currentCount,
            'filter'       => [
                'area'   => $areaFilter,
                'status' => $statusFilter,
                'q'      => $keyword,
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

    private function hasOverlap(
        int $areaId,
        int $barangId,
        string $startDate,
        ?string $endDate,
        ?int $exceptId = null
    ): bool {
        $builder = db_connect()->table('standar_barang_area')
            ->where('id_area', $areaId)
            ->where('id_barang', $barangId)
            ->where('status', 1);

        if ($exceptId !== null) {
            $builder->where('id_standar !=', $exceptId);
        }

        if ($endDate === null) {
            $builder->groupStart()
                ->where('berlaku_sampai', null)
                ->orWhere('berlaku_sampai >=', $startDate)
                ->groupEnd();
        } else {
            $builder->where('berlaku_mulai <=', $endDate)
                ->groupStart()
                    ->where('berlaku_sampai', null)
                    ->orWhere('berlaku_sampai >=', $startDate)
                ->groupEnd();
        }

        return $builder->countAllResults() > 0;
    }

    private function validatedInput(?int $exceptId = null): array
    {
        $areaRaw = trim((string) $this->request->getPost('id_area'));
        $barangRaw = trim((string) $this->request->getPost('id_barang'));
        $jumlahRaw = trim((string) $this->request->getPost('jumlah_standar'));
        $mulai = trim((string) $this->request->getPost('berlaku_mulai'));
        $sampai = trim((string) $this->request->getPost('berlaku_sampai'));
        $keterangan = trim((string) $this->request->getPost('keterangan'));
        $status = (string) ($this->request->getPost('status') ?? '1');
        $errors = [];

        $areaId = null;
        $barangId = null;

        if ($areaRaw === '' || !ctype_digit($areaRaw) || (int) $areaRaw < 1) {
            $errors[] = 'Area wajib dipilih.';
        } else {
            $areaId = (int) $areaRaw;
            if (!(new AreaModel())->where('status', 1)->find($areaId)) {
                $errors[] = 'Area tidak ditemukan atau sudah nonaktif.';
            }
        }

        if ($barangRaw === '' || !ctype_digit($barangRaw) || (int) $barangRaw < 1) {
            $errors[] = 'Barang wajib dipilih.';
        } else {
            $barangId = (int) $barangRaw;
            if (!(new BarangModel())->where('status', 1)->find($barangId)) {
                $errors[] = 'Barang tidak ditemukan atau sudah nonaktif.';
            }
        }

        if (!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/', $jumlahRaw) || (float) $jumlahRaw <= 0) {
            $errors[] = 'Jumlah standar harus lebih dari nol dan maksimal dua angka desimal.';
        }

        if (!$this->isValidDate($mulai)) {
            $errors[] = 'Tanggal Berlaku Mulai wajib diisi dengan tanggal yang benar.';
        }

        $sampaiValue = null;
        if ($sampai !== '') {
            if (!$this->isValidDate($sampai)) {
                $errors[] = 'Tanggal Berlaku Sampai tidak valid.';
            } elseif ($this->isValidDate($mulai) && $sampai < $mulai) {
                $errors[] = 'Tanggal Berlaku Sampai tidak boleh sebelum Berlaku Mulai.';
            } else {
                $sampaiValue = $sampai;
            }
        }

        if (strlen($keterangan) > 5000) {
            $errors[] = 'Keterangan maksimal 5.000 karakter.';
        }

        if (!in_array($status, ['0', '1'], true)) {
            $errors[] = 'Status Kebutuhan Area tidak valid.';
        }

        if (
            $areaId !== null
            && $barangId !== null
            && $this->isValidDate($mulai)
            && ($sampai === '' || $sampaiValue !== null)
            && $status === '1'
            && $this->hasOverlap($areaId, $barangId, $mulai, $sampaiValue, $exceptId)
        ) {
            $errors[] = 'Periode standar barang ini bertumpuk dengan standar aktif lain pada Area yang sama. Akhiri periode sebelumnya terlebih dahulu.';
        }

        return [[
            'id_area'        => $areaId,
            'id_barang'      => $barangId,
            'jumlah_standar' => $errors && !preg_match('/^\d{1,12}(?:\.\d{1,2})?$/', $jumlahRaw)
                ? null
                : number_format((float) $jumlahRaw, 2, '.', ''),
            'berlaku_mulai'  => $mulai,
            'berlaku_sampai' => $sampaiValue,
            'keterangan'     => $keterangan === '' ? null : $keterangan,
            'status'         => (int) $status,
        ], array_values(array_unique($errors))];
    }

    public function store()
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        [$data, $errors] = $this->validatedInput();
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data['created_by'] = (int) (session()->get('user_id') ?? 0) ?: null;

        if (!(new StandarBarangAreaModel())->insert($data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Kebutuhan Area gagal disimpan. Periksa apakah periode dan barang sudah terdaftar.']);
        }

        return redirect()->to('pendataan/master/kebutuhan-area')
            ->with('success', 'Kebutuhan Area berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new StandarBarangAreaModel();
        if (!$model->find($id)) {
            return redirect()->to('pendataan/master/kebutuhan-area')
                ->with('error', 'Data Kebutuhan Area tidak ditemukan.');
        }

        [$data, $errors] = $this->validatedInput($id);
        if ($errors) {
            return redirect()->back()->withInput()
                ->with('errors', $errors)
                ->with('edit_standar_id', $id);
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Perubahan Kebutuhan Area gagal disimpan.'])
                ->with('edit_standar_id', $id);
        }

        return redirect()->to('pendataan/master/kebutuhan-area')
            ->with('success', 'Kebutuhan Area berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new StandarBarangAreaModel();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to('pendataan/master/kebutuhan-area')
                ->with('error', 'Data Kebutuhan Area tidak ditemukan.');
        }

        $newStatus = (int) $item['status'] === 1 ? 0 : 1;

        if ($newStatus === 1 && $this->hasOverlap(
            (int) $item['id_area'],
            (int) $item['id_barang'],
            $item['berlaku_mulai'],
            $item['berlaku_sampai'] ?: null,
            $id
        )) {
            return redirect()->to('pendataan/master/kebutuhan-area')
                ->with('error', 'Standar tidak dapat diaktifkan karena periodenya bertumpuk dengan standar aktif lain.');
        }

        if (!$model->update($id, ['status' => $newStatus])) {
            return redirect()->to('pendataan/master/kebutuhan-area')
                ->with('error', 'Status Kebutuhan Area gagal diperbarui.');
        }

        return redirect()->to('pendataan/master/kebutuhan-area')
            ->with('success', $newStatus === 1
                ? 'Kebutuhan Area berhasil diaktifkan.'
                : 'Kebutuhan Area berhasil dinonaktifkan.');
    }
}
