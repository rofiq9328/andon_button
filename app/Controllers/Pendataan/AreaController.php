<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Libraries\Roles;
use App\Models\Pendataan\AreaModel;
use App\Models\Pendataan\FactoryModel;

class AreaController extends BaseController
{
    private const JENIS_AREA = [
        'LINE',
        'OFFICE',
        'WAREHOUSE',
        'MAINTENANCE',
        'DEPARTMENT',
        'OTHER',
    ];

    private function adminOnly()
    {
        if (!Roles::has('admin')) {
            session()->setFlashdata('error', 'Hanya Admin yang dapat mengubah data Area.');
            return redirect()->to('pendataan/master/area');
        }

        return null;
    }

    public function index()
    {
        $areas = db_connect()->table('area a')
            ->select('a.*, f.kode_factory, f.nama_factory, p.kode_area AS parent_kode, p.nama_area AS parent_nama')
            ->join('factory f', 'f.id_factory = a.id_factory', 'left')
            ->join('area p', 'p.id_area = a.parent_area_id', 'left')
            ->orderBy('a.cakupan_area', 'ASC')
            ->orderBy('f.kode_factory', 'ASC')
            ->orderBy('a.kode_area', 'ASC')
            ->get()
            ->getResultArray();

        $factories = (new FactoryModel())
            ->orderBy('kode_factory', 'ASC')
            ->findAll();

        $parentAreas = (new AreaModel())
            ->orderBy('kode_area', 'ASC')
            ->findAll();

        return view('pendataan/master/area', [
            'title'       => 'Master Area',
            'areas'       => $areas,
            'factories'   => $factories,
            'parentAreas' => $parentAreas,
        ]);
    }

    private function validatedInput(?int $exceptId = null): array
    {
        $model = new AreaModel();

        $scope      = strtoupper(trim((string) $this->request->getPost('cakupan_area')));
        $factoryRaw = trim((string) $this->request->getPost('id_factory'));
        $parentRaw  = trim((string) $this->request->getPost('parent_area_id'));
        $kode       = strtoupper(trim((string) $this->request->getPost('kode_area')));
        $nama       = trim((string) $this->request->getPost('nama_area'));
        $jenis      = strtoupper(trim((string) $this->request->getPost('jenis_area')));
        $deskripsi  = trim((string) $this->request->getPost('deskripsi'));
        $status     = (string) ($this->request->getPost('status') ?? '1');

        $errors = [];
        $idFactory = null;
        $parentId = null;

        if (!in_array($scope, ['PUSAT', 'FACTORY'], true)) {
            $errors[] = 'Cakupan Area tidak valid.';
        }

        if ($scope === 'FACTORY') {
            if ($factoryRaw === '' || !ctype_digit($factoryRaw) || (int) $factoryRaw < 1) {
                $errors[] = 'Factory wajib dipilih untuk Area dengan cakupan Factory.';
            } else {
                $factory = (new FactoryModel())->find((int) $factoryRaw);
                if (!$factory) {
                    $errors[] = 'Factory yang dipilih tidak ditemukan.';
                } else {
                    $idFactory = (int) $factoryRaw;
                }
            }
        }

        if ($parentRaw !== '') {
            if (!ctype_digit($parentRaw) || (int) $parentRaw < 1) {
                $errors[] = 'Induk Area tidak valid.';
            } else {
                $parentId = (int) $parentRaw;
                $parent = $model->find($parentId);

                if (!$parent) {
                    $errors[] = 'Induk Area yang dipilih tidak ditemukan.';
                } else {
                    if ($parent['cakupan_area'] !== $scope) {
                        $errors[] = 'Induk Area harus memiliki cakupan yang sama.';
                    } elseif (
                        $scope === 'FACTORY'
                        && (int) $parent['id_factory'] !== (int) $idFactory
                    ) {
                        $errors[] = 'Induk Area harus berasal dari Factory yang sama.';
                    }
                }

                if ($exceptId !== null && $parentId === $exceptId) {
                    $errors[] = 'Area tidak dapat menjadi induk bagi dirinya sendiri.';
                } elseif ($exceptId !== null && $parent) {
                    // Cegah relasi melingkar: induk yang dipilih tidak boleh berada
                    // di bawah Area yang sedang diedit.
                    $cursor = $parentId;
                    $visited = [];

                    while ($cursor > 0 && !isset($visited[$cursor])) {
                        if ($cursor === $exceptId) {
                            $errors[] = 'Induk Area tidak valid karena akan membentuk relasi melingkar.';
                            break;
                        }

                        $visited[$cursor] = true;
                        $ancestor = $model->find($cursor);
                        $cursor = $ancestor ? (int) ($ancestor['parent_area_id'] ?? 0) : 0;
                    }
                }
            }
        }

        if ($kode === '') {
            $errors[] = 'Kode Area wajib diisi.';
        } elseif (strlen($kode) > 30) {
            $errors[] = 'Kode Area maksimal 30 karakter.';
        } elseif (!preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $errors[] = 'Kode Area hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
        }

        if ($nama === '') {
            $errors[] = 'Nama Area wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $errors[] = 'Nama Area maksimal 100 karakter.';
        }

        if (!in_array($jenis, self::JENIS_AREA, true)) {
            $errors[] = 'Jenis Area tidak valid.';
        }

        if (!in_array($status, ['0', '1'], true)) {
            $errors[] = 'Status Area tidak valid.';
        }

        if ($kode !== '' && preg_match('/^[A-Z0-9._-]+$/', $kode)) {
            $duplicateQuery = $model->where('kode_area', $kode);

            if ($exceptId !== null) {
                $duplicateQuery->where('id_area !=', $exceptId);
            }

            if ($duplicateQuery->first()) {
                $errors[] = 'Kode Area sudah digunakan.';
            }
        }

        return [[
            'id_factory'     => $idFactory,
            'parent_area_id' => $parentId,
            'cakupan_area'   => $scope,
            'kode_area'      => $kode,
            'nama_area'      => $nama,
            'jenis_area'     => $jenis,
            'deskripsi'      => $deskripsi === '' ? null : $deskripsi,
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

        if (!(new AreaModel())->insert($data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Data Area gagal disimpan. Periksa kembali data yang dimasukkan.']);
        }

        return redirect()->to('pendataan/master/area')
            ->with('success', 'Data Area berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new AreaModel();
        if (!$model->find($id)) {
            return redirect()->to('pendataan/master/area')
                ->with('error', 'Data Area tidak ditemukan.');
        }

        [$data, $errors] = $this->validatedInput($id);

        if ($errors) {
            return redirect()->back()->withInput()
                ->with('errors', $errors)
                ->with('edit_area_id', $id);
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()
                ->with('errors', ['Perubahan data Area gagal disimpan.'])
                ->with('edit_area_id', $id);
        }

        return redirect()->to('pendataan/master/area')
            ->with('success', 'Data Area berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        if ($response = $this->adminOnly()) {
            return $response;
        }

        $model = new AreaModel();
        $area = $model->find($id);

        if (!$area) {
            return redirect()->to('pendataan/master/area')
                ->with('error', 'Data Area tidak ditemukan.');
        }

        $newStatus = (int) $area['status'] === 1 ? 0 : 1;

        if (!$model->update($id, ['status' => $newStatus])) {
            return redirect()->to('pendataan/master/area')
                ->with('error', 'Status Area gagal diperbarui.');
        }

        return redirect()->to('pendataan/master/area')
            ->with('success', $newStatus === 1
                ? 'Area berhasil diaktifkan.'
                : 'Area berhasil dinonaktifkan.');
    }
}
