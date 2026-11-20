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
