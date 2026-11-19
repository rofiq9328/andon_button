<?php

namespace App\Filters;

use App\Libraries\Roles;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Contoh pemakaian di routes:
     * $routes->group('', ['filter' => 'role:admin,supervisor'], function ($routes) { ... });
     *
     * User lolos jika salah satu role-nya termasuk daftar di atas.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments) || !Roles::hasAny($arguments)) {
            session()->setFlashdata('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            return redirect()->to('/andon');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
