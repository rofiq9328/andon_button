<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        if (!session()->get('pb_user')) {
            return redirect()->to('pendataan/login');
        }

        return view('pendataan/dashboard', [
            'title' => 'Dashboard Pendataan Barang',
        ]);
    }
}
