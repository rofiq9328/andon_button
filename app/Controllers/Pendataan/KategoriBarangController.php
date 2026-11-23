<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Libraries\Roles;
use App\Models\Pendataan\KategoriBarangModel;

class KategoriBarangController extends BaseController
{
    private function adminOnly()
    {
        if (!Roles::has('admin')) {
            session()->setFlashdata('error', 'Hanya Admin yang dapat mengubah kategori barang.');
            return redirect()->to('pendataan/master/kategori');
        }

        return null;
    }

    public function index()
    {
        $categories = (new KategoriBarangModel())
            ->orderBy('kode_kategori', 'ASC')
            ->findAll();

        $usageRows = db_connect()->table('barang')
            ->select('id_kategori, COUNT(*) AS total', false)
            ->groupBy('id_kategori')
            ->get()
            ->getResultArray();

        $usageCounts = [];
        foreach ($usageRows as $usageRow) {
            $usageCounts[(int) $usageRow['id_kategori']] = (int) $usageRow['total'];
        }

        foreach ($categories as &$category) {
            $category['jumlah_barang'] = $usageCounts[(int) $category['id_kategori']] ?? 0;
        }
        unset($category);

        return view('pendataan/master/kategori', [
            'title'      => 'Master Kategori Barang',
            'categories' => $categories,
        ]);
    }

    private function validatedInput(?int $exceptId = null): array
    {
        $kode        = strtoupper(trim((string) $this->request->getPost('kode_kategori')));
        $nama        = trim((string) $this->request->getPost('nama_kategori'));
        $deskripsi   = trim((string) $this->request->getPost('deskripsi'));
        $status      = (string) ($this->request->getPost('status') ?? '1');

        $errors = [];

        if ($kode === '') {
            $errors[] = 'Kode Kategori wajib diisi.';
        } elseif (strlen($kode) > 20) {
            $errors[] = 'Kode Kategori maksimal 20 karakter.';
        } elseif (!preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $errors[] = 'Kode Kategori hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
        }

        if ($nama === '') {
            $errors[] = 'Nama Kategori wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $errors[] = 'Nama Kategori maksimal 100 karakter.';
        }

        if (!in_array($status, ['0', '1'], true)) {
            $errors[] = 'Status Kategori tidak valid.';
        }

        if ($kode !== '' && preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $duplicateQuery = (new KategoriBarangModel())
                ->where('kode_kategori', $kode);

            if ($exceptId !== null) {
                $duplicateQuery->where('id_kategori !=', $exceptId);
            }

            if ($duplicateQuery->first()) {
                $errors[] = 'Kode Kategori sudah digunakan.';
            }
        }

        return [[
            'kode_kategori' => $kode,
            'nama_kategori' => $nama,
            'deskripsi'     => $deskripsi === '' ? null : $deskripsi,
            'status'        => (int) $status,
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

        if (!(new KategoriBarangModel())->insert($data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Kategori Barang gagal disimpan. Periksa kembali data yang dimasukkan.']);
        }

        return redirect()->to('pendataan/master/kategori')
            ->with('success', 'Kategori Barang berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new KategoriBarangModel();
        if (!$model->find($id)) {
            return redirect()->to('pendataan/master/kategori')
                ->with('error', 'Kategori Barang tidak ditemukan.');
        }

        [$data, $errors] = $this->validatedInput($id);

        if ($errors) {
            return redirect()->back()->withInput()
                ->with('errors', $errors)
                ->with('edit_kategori_id', $id);
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Perubahan Kategori Barang gagal disimpan.'])
                ->with('edit_kategori_id', $id);
        }

        return redirect()->to('pendataan/master/kategori')
            ->with('success', 'Kategori Barang berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $db = db_connect();
        $db->transBegin();

        // Lock category row while checking references to avoid a concurrent
        // item insert between the usage check and category deletion.
        $category = $db->query(
            'SELECT id_kategori, nama_kategori
             FROM kategori_barang
             WHERE id_kategori = ?
             FOR UPDATE',
            [$id]
        )->getRowArray();

        if (!$category) {
            $db->transRollback();
            return redirect()->to('pendataan/master/kategori')
                ->with('error', 'Kategori Barang tidak ditemukan.');
        }

        $usedCount = $db->table('barang')
            ->where('id_kategori', $id)
            ->countAllResults();

        if ($usedCount > 0) {
            $db->transRollback();
            return redirect()->to('pendataan/master/kategori')
                ->with('error', 'Kategori "' . $category['nama_kategori'] . '" tidak dapat dihapus karena masih digunakan oleh '
                    . $usedCount . ' data barang. Ubah kategori barang terkait atau nonaktifkan kategori ini.');
        }

        $deleted = $db->table('kategori_barang')
            ->where('id_kategori', $id)
            ->delete();

        if (!$deleted || $db->affectedRows() !== 1 || !$db->transStatus()) {
            $db->transRollback();
            return redirect()->to('pendataan/master/kategori')
                ->with('error', 'Kategori Barang gagal dihapus. Data tidak berubah.');
        }

        $db->transCommit();

        return redirect()->to('pendataan/master/kategori')
            ->with('success', 'Kategori "' . $category['nama_kategori'] . '" berhasil dihapus.');
    }

    public function toggleStatus(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new KategoriBarangModel();
        $category = $model->find($id);

        if (!$category) {
            return redirect()->to('pendataan/master/kategori')
                ->with('error', 'Kategori Barang tidak ditemukan.');
        }

        $newStatus = (int) $category['status'] === 1 ? 0 : 1;

        if (!$model->update($id, ['status' => $newStatus])) {
            return redirect()->to('pendataan/master/kategori')
                ->with('error', 'Status Kategori Barang gagal diperbarui.');
        }

        return redirect()->to('pendataan/master/kategori')
            ->with('success', $newStatus === 1
                ? 'Kategori Barang berhasil diaktifkan.'
                : 'Kategori Barang berhasil dinonaktifkan.');
    }
}
