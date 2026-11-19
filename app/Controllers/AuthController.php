<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function loginView()
    {
        // Kalau sudah login, langsung lempar ke halaman utama
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/andon');
        }

        return view('auth/login');
    }

    public function login()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            session()->setFlashdata('error', 'Username atau password salah.');
            return redirect()->back()->withInput();
        }

        if (!$user['is_active']) {
            session()->setFlashdata('error', 'Akun Anda nonaktif. Hubungi administrator.');
            return redirect()->back()->withInput();
        }

        // Semua role user (bisa lebih dari satu)
        $roles = (new \App\Models\UserRoleModel())->rolesForUser((int) $user['id']);
        if (!in_array($user['role'], $roles, true)) {
            array_unshift($roles, $user['role']);
        }

        // Simpan data penting saja ke session, JANGAN simpan password
        session()->regenerate(); // cegah session fixation
        session()->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'full_name'  => $user['full_name'],
            'role'       => $user['role'],
            'roles'      => $roles,
            'isLoggedIn' => true,
        ]);

        return redirect()->to('/andon');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
