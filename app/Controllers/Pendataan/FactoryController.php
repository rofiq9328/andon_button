<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Libraries\Roles;
use App\Models\Pendataan\FactoryModel;

class FactoryController extends BaseController
{
    private function adminOnly()
    {
        if (!Roles::has('admin')) {
            session()->setFlashdata('error', 'Hanya admin yang dapat mengubah data Factory.');
            return redirect()->to('pendataan/master/factory');
        }

        return null;
    }

    public function index()
    {
        $factories = (new FactoryModel())
            ->orderBy('kode_factory', 'ASC')
            ->findAll();

        return view('pendataan/master/factory', [
            'title'     => 'Master Factory',
            'factories' => $factories,
        ]);
    }

    private function validatedInput(?int $exceptId = null): array
    {
        $kode   = strtoupper(trim((string) $this->request->getPost('kode_factory')));
        $nama   = trim((string) $this->request->getPost('nama_factory'));
        $lokasi = trim((string) $this->request->getPost('lokasi'));
        $status = (string) ($this->request->getPost('status') ?? '1');

        $errors = [];

        if ($kode === '') {
            $errors[] = 'Kode Factory wajib diisi.';
        } elseif (strlen($kode) > 10) {
            $errors[] = 'Kode Factory maksimal 10 karakter.';
        } elseif (!preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $errors[] = 'Kode Factory hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
        }

        if ($nama === '') {
            $errors[] = 'Nama Factory wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $errors[] = 'Nama Factory maksimal 100 karakter.';
        }

        if (strlen($lokasi) > 255) {
            $errors[] = 'Lokasi maksimal 255 karakter.';
        }

        if (!in_array($status, ['0', '1'], true)) {
            $errors[] = 'Status Factory tidak valid.';
        }

        if ($kode !== '' && preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $duplicate = (new FactoryModel())
                ->where('kode_factory', $kode)
                ->first();

            if ($duplicate && (int) $duplicate['id_factory'] !== (int) $exceptId) {
                $errors[] = 'Kode Factory sudah digunakan.';
            }
        }

        return [[
            'kode_factory' => $kode,
            'nama_factory' => $nama,
            'lokasi'       => $lokasi === '' ? null : $lokasi,
            'status'       => (int) $status,
        ], $errors];
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

        if (!(new FactoryModel())->insert($data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Data Factory gagal disimpan. Periksa kembali data yang dimasukkan.']);
        }

        return redirect()->to('pendataan/master/factory')
            ->with('success', 'Data Factory berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new FactoryModel();
        $factory = $model->find($id);

        if (!$factory) {
            return redirect()->to('pendataan/master/factory')
                ->with('error', 'Data Factory tidak ditemukan.');
        }

        [$data, $errors] = $this->validatedInput($id);

        if ($errors) {
            return redirect()->back()->withInput()
                ->with('errors', $errors)
                ->with('edit_factory_id', $id);
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Perubahan data Factory gagal disimpan.'])
                ->with('edit_factory_id', $id);
        }

        return redirect()->to('pendataan/master/factory')
            ->with('success', 'Data Factory berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new FactoryModel();
        $factory = $model->find($id);

        if (!$factory) {
            return redirect()->to('pendataan/master/factory')
                ->with('error', 'Data Factory tidak ditemukan.');
        }

        $newStatus = (int) $factory['status'] === 1 ? 0 : 1;

        if (!$model->update($id, ['status' => $newStatus])) {
            return redirect()->to('pendataan/master/factory')
                ->with('error', 'Status Factory gagal diperbarui.');
        }

        return redirect()->to('pendataan/master/factory')
            ->with('success', $newStatus === 1
                ? 'Factory berhasil diaktifkan.'
                : 'Factory berhasil dinonaktifkan.');
    }
}
