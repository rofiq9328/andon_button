<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Libraries\Roles;
use App\Models\Pendataan\SatuanModel;

class SatuanController extends BaseController
{
    private function adminOnly()
    {
        if (!Roles::has('admin')) {
            session()->setFlashdata('error', 'Hanya Admin yang dapat mengubah data satuan.');
            return redirect()->to('pendataan/master/satuan');
        }

        return null;
    }

    public function index()
    {
        $units = (new SatuanModel())
            ->orderBy('kode_satuan', 'ASC')
            ->findAll();

        return view('pendataan/master/satuan', [
            'title' => 'Master Satuan',
            'units' => $units,
        ]);
    }

    private function validatedInput(?int $exceptId = null): array
    {
        $kode   = strtoupper(trim((string) $this->request->getPost('kode_satuan')));
        $nama   = trim((string) $this->request->getPost('nama_satuan'));
        $status = (string) ($this->request->getPost('status') ?? '1');

        $errors = [];

        if ($kode === '') {
            $errors[] = 'Kode Satuan wajib diisi.';
        } elseif (strlen($kode) > 10) {
            $errors[] = 'Kode Satuan maksimal 10 karakter.';
        } elseif (!preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $errors[] = 'Kode Satuan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
        }

        if ($nama === '') {
            $errors[] = 'Nama Satuan wajib diisi.';
        } elseif (strlen($nama) > 30) {
            $errors[] = 'Nama Satuan maksimal 30 karakter.';
        }

        if (!in_array($status, ['0', '1'], true)) {
            $errors[] = 'Status Satuan tidak valid.';
        }

        if ($kode !== '' && preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $duplicateQuery = (new SatuanModel())
                ->where('kode_satuan', $kode);

            if ($exceptId !== null) {
                $duplicateQuery->where('id_satuan !=', $exceptId);
            }

            if ($duplicateQuery->first()) {
                $errors[] = 'Kode Satuan sudah digunakan.';
            }
        }

        return [[
            'kode_satuan' => $kode,
            'nama_satuan' => $nama,
            'status'      => (int) $status,
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

        if (!(new SatuanModel())->insert($data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Data Satuan gagal disimpan. Periksa kembali data yang dimasukkan.']);
        }

        return redirect()->to('pendataan/master/satuan')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new SatuanModel();
        if (!$model->find($id)) {
            return redirect()->to('pendataan/master/satuan')
                ->with('error', 'Data Satuan tidak ditemukan.');
        }

        [$data, $errors] = $this->validatedInput($id);

        if ($errors) {
            return redirect()->back()->withInput()
                ->with('errors', $errors)
                ->with('edit_satuan_id', $id);
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Perubahan Satuan gagal disimpan.'])
                ->with('edit_satuan_id', $id);
        }

        return redirect()->to('pendataan/master/satuan')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new SatuanModel();
        $unit = $model->find($id);

        if (!$unit) {
            return redirect()->to('pendataan/master/satuan')
                ->with('error', 'Data Satuan tidak ditemukan.');
        }

        $newStatus = (int) $unit['status'] === 1 ? 0 : 1;

        if (!$model->update($id, ['status' => $newStatus])) {
            return redirect()->to('pendataan/master/satuan')
                ->with('error', 'Status Satuan gagal diperbarui.');
        }

        return redirect()->to('pendataan/master/satuan')
            ->with('success', $newStatus === 1
                ? 'Satuan berhasil diaktifkan.'
                : 'Satuan berhasil dinonaktifkan.');
    }
}
