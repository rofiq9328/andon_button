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
        $routes->get('isi/line/(:num)', 'AuditController::linePic/$1');
        $routes->get('sesi/(:num)', 'AuditController::form/$1');
        $routes->get('sesi/(:num)/line/(:num)', 'AuditController::lineAdmin/$1/$2');
        $routes->post('simpan', 'AuditController::save');
        $routes->post('buka/(:num)', 'AuditController::reopen/$1');
    });

    // Penugasan PIC audit: hanya admin
    $routes->group('audit-andon/penugasan', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/', 'PicAssignmentController::index');
        $routes->post('simpan', 'PicAssignmentController::store');
        $routes->post('hapus/(:num)', 'PicAssignmentController::delete/$1');
    });
});