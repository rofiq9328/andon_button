<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

use App\Controllers\AndonController;
use App\Controllers\AuthController;
use App\Controllers\UserController;

// ========================================================
// ROUTE LOGIN / LOGOUT (tidak boleh dibungkus filter 'auth',
// kalau tidak akan infinite redirect ke halaman login sendiri)
// ========================================================
$routes->get('login', [AuthController::class, 'loginView']);
$routes->post('login', [AuthController::class, 'login']);
$routes->get('logout', [AuthController::class, 'logout']);

// ========================================================
// ROUTE YANG WAJIB LOGIN
// ========================================================
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // ------------------------------------------------------------------
    // Dashboard Andon: boleh diakses SEMUA role yang sudah login
    // (admin, supervisor, user) — ini halaman utamanya.
    // ------------------------------------------------------------------
    $routes->get('/', [AndonController::class, 'index']);
    $routes->get('andon', [AndonController::class, 'index']);

    // API status/clear ikut dashboard, jadi tetap bisa diakses semua role
    $routes->get('api/andon/status', [AndonController::class, 'status']);
    $routes->post('api/andon/clear', [AndonController::class, 'clear']);

    // --------------------------------------------------
    // Route yang HANYA boleh diakses role admin.
    // Selain admin akan otomatis dilempar balik ke dashboard.
    // --------------------------------------------------
    $routes->group('', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('home', 'Home::index');

        // Menampilkan halaman update (Settings > Master Button)
        $routes->get('andon/update-button', 'ButtonController::updateView');

        // Memproses upload file excel
        $routes->post('buttoncontroller/processExcel', 'ButtonController::processExcel');

        $routes->post('buttoncontroller/updateSingle', 'ButtonController::updateSingle');

        // Kosongkan semua data button (hanya admin, wajib konfirmasi)
        $routes->post('buttoncontroller/clearAll', 'ButtonController::clearAll');

        // Route untuk Delete Data
        $routes->get('buttoncontroller/delete/(:any)', 'ButtonController::delete/$1');

        // User Management
        $routes->get('users', [UserController::class, 'index']);
        $routes->post('users', [UserController::class, 'store']);
        $routes->post('users/update/(:num)', [UserController::class, 'update/$1']);
        $routes->post('users/delete/(:num)', [UserController::class, 'delete/$1']);
    });

    // Menu Audit Andon: admin dan PIC audit
    $routes->group('audit-andon', ['filter' => 'role:admin,pic_audit'], function ($routes) {
        $routes->get('/', 'AuditController::index');
        $routes->get('isi', 'AuditController::form');
        $routes->get('isi/review', 'AuditController::review');
        $routes->get('isi/line/(:num)', 'AuditController::linePic/$1');
        $routes->get('sesi/(:num)', 'AuditController::form/$1');
        $routes->get('sesi/(:num)/review', 'AuditController::review/$1');
        $routes->get('sesi/(:num)/line/(:num)', 'AuditController::lineAdmin/$1/$2');
        $routes->post('simpan', 'AuditController::save');
        $routes->post('autosave', 'AuditController::autosave');
        $routes->post('isi-cepat', 'AuditController::quickFill');
        $routes->post('buka/(:num)', 'AuditController::reopen/$1');
    });

    // Penugasan PIC audit: hanya admin
    $routes->group('audit-andon/penugasan', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/', 'PicAssignmentController::index');
        $routes->post('simpan', 'PicAssignmentController::store');
        $routes->post('hapus/(:num)', 'PicAssignmentController::delete/$1');
    });

    // Export Excel audit: hanya admin
    $routes->group('audit-andon/export', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/', 'AuditExportController::excel');
    });

    // Pendataan barang: memakai login andon (satu login)
    $routes->group('pendataan', ['filter' => 'auth'], function ($routes) {
        $rolesPendataan = ['filter' => 'role:admin,petugas_area,verifikator,viewer'];

        $routes->get('/', 'Pendataan\HomeController::index', $rolesPendataan);

        // Master Factory: dapat dilihat role Pendataan, perubahan hanya Admin.
        $routes->get('master/factory', 'Pendataan\FactoryController::index', $rolesPendataan);
        $routes->post('master/factory/simpan', 'Pendataan\FactoryController::store', ['filter' => 'role:admin']);
        $routes->post('master/factory/ubah/(:num)', 'Pendataan\FactoryController::update/$1', ['filter' => 'role:admin']);
        $routes->post('master/factory/status/(:num)', 'Pendataan\FactoryController::toggleStatus/$1', ['filter' => 'role:admin']);

        // Master Area: dapat dilihat role Pendataan, perubahan hanya Admin.
        $routes->get('master/area', 'Pendataan\AreaController::index', $rolesPendataan);
        $routes->post('master/area/simpan', 'Pendataan\AreaController::store', ['filter' => 'role:admin']);
        $routes->post('master/area/ubah/(:num)', 'Pendataan\AreaController::update/$1', ['filter' => 'role:admin']);
        $routes->post('master/area/status/(:num)', 'Pendataan\AreaController::toggleStatus/$1', ['filter' => 'role:admin']);

        // Master Kategori Barang: dapat dilihat role Pendataan, perubahan hanya Admin.
        $routes->get('master/kategori', 'Pendataan\KategoriBarangController::index', $rolesPendataan);
        $routes->post('master/kategori/simpan', 'Pendataan\KategoriBarangController::store', ['filter' => 'role:admin']);
        $routes->post('master/kategori/ubah/(:num)', 'Pendataan\KategoriBarangController::update/$1', ['filter' => 'role:admin']);
        $routes->post('master/kategori/status/(:num)', 'Pendataan\KategoriBarangController::toggleStatus/$1', ['filter' => 'role:admin']);

        // Master Satuan: dapat dilihat role Pendataan, perubahan hanya Admin.
        $routes->get('master/satuan', 'Pendataan\SatuanController::index', $rolesPendataan);
        $routes->post('master/satuan/simpan', 'Pendataan\SatuanController::store', ['filter' => 'role:admin']);
        $routes->post('master/satuan/ubah/(:num)', 'Pendataan\SatuanController::update/$1', ['filter' => 'role:admin']);
        $routes->post('master/satuan/status/(:num)', 'Pendataan\SatuanController::toggleStatus/$1', ['filter' => 'role:admin']);

        // Master Barang: dapat dilihat role Pendataan, perubahan hanya Admin.
        $routes->get('master/barang', 'Pendataan\BarangController::index', $rolesPendataan);
        $routes->post('master/barang/simpan', 'Pendataan\BarangController::store', ['filter' => 'role:admin']);
        $routes->post('master/barang/ubah/(:num)', 'Pendataan\BarangController::update/$1', ['filter' => 'role:admin']);
        $routes->post('master/barang/status/(:num)', 'Pendataan\BarangController::toggleStatus/$1', ['filter' => 'role:admin']);

        // Riwayat transaksi: dapat dilihat seluruh role Pendataan.
        $routes->get('transaksi', 'Pendataan\TransaksiController::index', $rolesPendataan);
        $routes->get('transaksi/detail/(:num)', 'Pendataan\TransaksiController::detail/$1', $rolesPendataan);

        // Pembuatan transaksi Draft: Admin dan Petugas Area.
        $routes->get('transaksi/tambah', 'Pendataan\TransaksiController::create', ['filter' => 'role:admin,petugas_area']);
        $routes->post('transaksi/simpan', 'Pendataan\TransaksiController::store', ['filter' => 'role:admin,petugas_area']);
    });
});