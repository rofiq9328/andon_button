<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Contoh pemakaian di routes:
     * $routes->group('', ['filter' => 'role:admin,supervisor'], function ($routes) { ... });
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $userRole = session()->get('role');

        if (empty($arguments) || !in_array($userRole, $arguments, true)) {
            session()->setFlashdata('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            return redirect()->to('/andon');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
