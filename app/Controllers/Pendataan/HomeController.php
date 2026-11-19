<?php

namespace App\Controllers\Pendataan;

use App\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        return view('pendataan/dashboard', [
            'title' => 'Dashboard Pendataan Barang',
        ]);
    }
}
