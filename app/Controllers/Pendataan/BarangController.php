<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Libraries\Roles;
use App\Models\Pendataan\BarangModel;
use App\Models\Pendataan\KategoriBarangModel;
use App\Models\Pendataan\SatuanModel;

class BarangController extends BaseController
{
    private function adminOnly()
    {
        if (!Roles::has('admin')) {
            session()->setFlashdata('error', 'Hanya Admin yang dapat mengubah data barang.');
            return redirect()->to('pendataan/master/barang');
        }

        return null;
    }

    public function index()
    {
        $db = db_connect();
        $items = $db->table('barang b')
            ->select('b.*, k.kode_kategori, k.nama_kategori, s.kode_satuan, s.nama_satuan')
            ->join('kategori_barang k', 'k.id_kategori = b.id_kategori')
            ->join('satuan s', 's.id_satuan = b.id_satuan')
            ->orderBy('b.nama_barang', 'ASC')
            ->get()->getResultArray();

        $categories = (new KategoriBarangModel())->where('status', 1)->orderBy('nama_kategori')->findAll();
        $units = (new SatuanModel())->where('status', 1)->orderBy('nama_satuan')->findAll();

        return view('pendataan/master/barang', [
            'title' => 'Master Barang',
            'items' => $items,
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    private function validatedInput(?int $exceptId = null): array
    {
        $kode = strtoupper(trim((string) $this->request->getPost('kode_barang')));
        $nama = trim((string) $this->request->getPost('nama_barang'));
        $kategori = (int) $this->request->getPost('id_kategori');
        $satuan = (int) $this->request->getPost('id_satuan');
        $merek = trim((string) $this->request->getPost('merek'));
        $modelTipe = trim((string) $this->request->getPost('model_tipe'));
        $spesifikasi = trim((string) $this->request->getPost('spesifikasi'));
        $deskripsi = trim((string) $this->request->getPost('deskripsi'));
        $status = (string) ($this->request->getPost('status') ?? '1');
        $errors = [];

        if ($kode === '') {
            $errors[] = 'Kode Barang wajib diisi.';
        } elseif (strlen($kode) > 30 || !preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $errors[] = 'Kode Barang maksimal 30 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
        }
        if ($nama === '') {
            $errors[] = 'Nama Barang wajib diisi.';
        } elseif (strlen($nama) > 150) {
            $errors[] = 'Nama Barang maksimal 150 karakter.';
        }
        if (!(new KategoriBarangModel())->where('id_kategori', $kategori)->where('status', 1)->first()) {
            $errors[] = 'Kategori Barang wajib dipilih dan harus berstatus aktif.';
        }
        if (!(new SatuanModel())->where('id_satuan', $satuan)->where('status', 1)->first()) {
            $errors[] = 'Satuan wajib dipilih dan harus berstatus aktif.';
        }
        if (strlen($merek) > 100) $errors[] = 'Merek maksimal 100 karakter.';
        if (strlen($modelTipe) > 100) $errors[] = 'Model/Tipe maksimal 100 karakter.';
        if (!in_array($status, ['0', '1'], true)) $errors[] = 'Status Barang tidak valid.';

        if ($kode !== '' && preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $query = (new BarangModel())->where('kode_barang', $kode);
            if ($exceptId !== null) $query->where('id_barang !=', $exceptId);
            if ($query->first()) $errors[] = 'Kode Barang sudah digunakan.';
        }

        return [[
            'kode_barang' => $kode,
            'nama_barang' => $nama,
            'id_kategori' => $kategori,
            'id_satuan' => $satuan,
            'merek' => $merek === '' ? null : $merek,
            'model_tipe' => $modelTipe === '' ? null : $modelTipe,
            'spesifikasi' => $spesifikasi === '' ? null : $spesifikasi,
            'deskripsi' => $deskripsi === '' ? null : $deskripsi,
            'status' => (int) $status,
        ], array_values(array_unique($errors))];
    }

    private function uploadPhoto(?string $oldPhoto = null): array
    {
        $file = $this->request->getFile('foto_barang');
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) return [$oldPhoto, []];
        if (!$file->isValid()) return [$oldPhoto, ['Foto Barang gagal diunggah.']];
        if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return [$oldPhoto, ['Foto Barang harus berformat JPG, JPEG, PNG, atau WEBP.']];
        }
        if ($file->getSize() > (2 * 1024 * 1024)) return [$oldPhoto, ['Ukuran Foto Barang maksimal 2 MB.']];

        $directory = FCPATH . 'uploads/pendataan/barang';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return [$oldPhoto, ['Folder penyimpanan foto tidak dapat dibuat.']];
        }

        $newName = $file->getRandomName();
        if (!$file->move($directory, $newName)) return [$oldPhoto, ['Foto Barang gagal disimpan.']];
                return [$newName, []];
    }

    public function store()
    {
        if ($response = $this->adminOnly()) return $response;
        [$data, $errors] = $this->validatedInput();
        if ($errors) return redirect()->back()->withInput()->with('errors', $errors);
        [$photo, $photoErrors] = $this->uploadPhoto();
        if ($photoErrors) return redirect()->back()->withInput()->with('errors', $photoErrors);
        $data['foto_barang'] = $photo;
        $data['created_by'] = (int) (session()->get('user_id') ?? 0) ?: null;
        $data['updated_by'] = $data['created_by'];

        if (!(new BarangModel())->insert($data)) {
            if ($photo) @unlink(FCPATH . 'uploads/pendataan/barang/' . basename($photo));
            return redirect()->back()->withInput()->with('errors', ['Data Barang gagal disimpan.']);
        }
        return redirect()->to('pendataan/master/barang')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($response = $this->adminOnly()) return $response;
        $model = new BarangModel();
        $current = $model->find($id);
        if (!$current) return redirect()->to('pendataan/master/barang')->with('error', 'Barang tidak ditemukan.');

        [$data, $errors] = $this->validatedInput($id);
        if ($errors) return redirect()->back()->withInput()->with('errors', $errors)->with('edit_barang_id', $id);
        [$photo, $photoErrors] = $this->uploadPhoto($current['foto_barang'] ?? null);
        if ($photoErrors) return redirect()->back()->withInput()->with('errors', $photoErrors)->with('edit_barang_id', $id);
        $data['foto_barang'] = $photo;
        $data['updated_by'] = (int) (session()->get('user_id') ?? 0) ?: null;

        if (!$model->update($id, $data)) {
            if ($photo && $photo !== ($current['foto_barang'] ?? null)) {
                @unlink(FCPATH . 'uploads/pendataan/barang/' . basename($photo));
            }
            return redirect()->back()->withInput()->with('errors', ['Perubahan Barang gagal disimpan.'])->with('edit_barang_id', $id);
        }
        if (!empty($current['foto_barang']) && $photo !== $current['foto_barang']) {
            $oldPath = FCPATH . 'uploads/pendataan/barang/' . basename($current['foto_barang']);
            if (is_file($oldPath)) @unlink($oldPath);
        }
        return redirect()->to('pendataan/master/barang')->with('success', 'Barang berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        if ($response = $this->adminOnly()) return $response;
        $model = new BarangModel();
        $item = $model->find($id);
        if (!$item) return redirect()->to('pendataan/master/barang')->with('error', 'Barang tidak ditemukan.');
        $newStatus = (int) $item['status'] === 1 ? 0 : 1;
        if (!$model->update($id, ['status' => $newStatus, 'updated_by' => (int) (session()->get('user_id') ?? 0) ?: null])) {
            return redirect()->to('pendataan/master/barang')->with('error', 'Status Barang gagal diperbarui.');
        }
        return redirect()->to('pendataan/master/barang')->with('success', $newStatus === 1 ? 'Barang berhasil diaktifkan.' : 'Barang berhasil dinonaktifkan.');
    }
}
