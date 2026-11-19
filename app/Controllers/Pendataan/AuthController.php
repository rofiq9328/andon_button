<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;
use App\Models\Pendataan\AppUserModel;
use App\Models\Pendataan\RoleModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('pb_user')) {
            return redirect()->to('pendataan');
        }

        return view('pendataan/login');
    }

    public function prosesLogin()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        $userModel = new AppUserModel();
        $user      = $userModel->where('username', $username)->where('status', 1)->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()
                ->with('error', 'Username atau password salah, atau akun tidak aktif.');
        }

        $role = (new RoleModel())->find($user['id_role']);

        session()->regenerate();
        session()->set('pb_user', [
            'id_user'   => (int) $user['id_user'],
            'nama_user' => $user['nama_user'],
            'username'  => $user['username'],
            'id_role'   => (int) $user['id_role'],
            'kode_role' => $role['kode_role'] ?? '',
        ]);

        $userModel->update($user['id_user'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('pendataan');
    }

    public function logout()
    {
        session()->remove('pb_user');

        return redirect()->to('pendataan/login')->with('success', 'Anda sudah keluar.');
    }
}
