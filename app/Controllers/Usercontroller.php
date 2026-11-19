<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $search  = trim((string) $this->request->getGet('search'));
        $role    = $this->request->getGet('role') ?? 'all';
        $perPage = 10;

        $builder = $this->userModel->orderBy('created_at', 'DESC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('username', $search)
                ->orLike('full_name', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        if ($role !== 'all' && in_array($role, UserRoleModel::ALL_ROLES, true)) {
            // Filter memakai semua role user, bukan hanya role utama
            $ids = array_column(
                db_connect()->table('user_roles')->select('user_id')->where('role', $role)->get()->getResultArray(),
                'user_id'
            );
            $builder->whereIn('id', $ids ?: [0]);
        }

        // withQuery() tidak ada di CI4 Pager, query string ikut otomatis ke link pagination
        $users = $builder->paginate($perPage);
        $pager = $this->userModel->pager;

        $userRoles = (new UserRoleModel())->rolesForUsers(array_column($users, 'id'));

        return view('user/management_user', [
            'users'     => $users,
            'pager'     => $pager,
            'search'    => $search,
            'role'      => $role,
            'allRoles'  => UserRoleModel::LABELS,
            'userRoles' => $userRoles,
        ]);
    }

    public function store()
    {
        $roles = UserRoleModel::sanitize($this->request->getPost('roles'));

        $rules = [
            'username'  => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'full_name' => 'required|max_length[100]',
            'email'     => 'permit_empty|valid_email|is_unique[users.email]',
            'password'  => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));
            return redirect()->to('users')->withInput();
        }

        if (empty($roles)) {
            session()->setFlashdata('error', 'Pilih minimal satu role.');
            return redirect()->to('users')->withInput();
        }

        $username = $this->request->getPost('username');

        $success = $this->userModel->saveWithHashedPassword([
            'username'  => $username,
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email') ?: null,
            'password'  => $this->request->getPost('password'),
            'role'      => $roles[0], // role utama = role pertama yang dipilih
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        if ($success) {
            $newUser = $this->userModel->where('username', $username)->first();
            if ($newUser) {
                (new UserRoleModel())->syncRoles((int) $newUser['id'], $roles);
            }
            session()->setFlashdata('success', 'User baru berhasil ditambahkan.');
        } else {
            $errors = $this->userModel->errors();
            session()->setFlashdata('error', $errors ? implode(' ', $errors) : 'Gagal menambahkan user baru.');
        }

        return redirect()->to('users');
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            session()->setFlashdata('error', 'User tidak ditemukan.');
            return redirect()->to('users');
        }

        $roles = UserRoleModel::sanitize($this->request->getPost('roles'));

        $rules = [
            'username'  => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'full_name' => 'required|max_length[100]',
            'email'     => "permit_empty|valid_email|is_unique[users.email,id,{$id}]",
        ];

        // Password opsional saat edit: kalau diisi baru divalidasi dan diganti
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));
            return redirect()->to('users');
        }

        if (empty($roles)) {
            session()->setFlashdata('error', 'Pilih minimal satu role.');
            return redirect()->to('users');
        }

        $success = $this->userModel->saveWithHashedPassword([
            'username'  => $this->request->getPost('username'),
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email') ?: null,
            'password'  => $this->request->getPost('password'), // kosong = tidak diganti (lihat UserModel)
            'role'      => $roles[0],
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ], (int) $id);

        if ($success) {
            (new UserRoleModel())->syncRoles((int) $id, $roles);
            session()->setFlashdata('success', 'Data user berhasil diperbarui.');
        } else {
            $errors = $this->userModel->errors();
            session()->setFlashdata('error', $errors ? implode(' ', $errors) : 'Gagal memperbarui data user.');
        }

        return redirect()->to('users');
    }

    public function delete($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            session()->setFlashdata('error', 'User tidak ditemukan.');
            return redirect()->to('users');
        }

        // Cegah user menghapus akunnya sendiri yang sedang login
        if ((int) $id === (int) session()->get('user_id')) {
            session()->setFlashdata('error', 'Tidak bisa menghapus akun yang sedang kamu gunakan untuk login.');
            return redirect()->to('users');
        }

        $this->userModel->delete($id);
        session()->setFlashdata('success', "User \"{$user['username']}\" berhasil dihapus.");
        return redirect()->to('users');
    }
}
