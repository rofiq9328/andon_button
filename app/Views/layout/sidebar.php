<?php

$request_uri  = strtok($_SERVER['REQUEST_URI'], '?');
$request_uri  = str_replace('index.php', '', $request_uri);
$request_uri  = preg_replace('#/+#', '/', $request_uri);
$base_path    = trim(parse_url(base_url(), PHP_URL_PATH), '/');
$current_path = trim($request_uri, '/');

if ($base_path !== '' && strpos($current_path, $base_path) === 0) {
    $current_path = trim(substr($current_path, strlen($base_path)), '/');
}

function isMenuActive($path, $current_path)
{
    $path = trim($path, '/');

    if ($path === '') {
        return $current_path === '';
    }

    return $current_path === $path || strpos($current_path, $path . '/') === 0;
}

/*
 * Status menu aktif untuk setiap kelompok.
 */
$isDashboardActive       = isMenuActive('', $current_path);
$isButtonIdActive        = isMenuActive('andon/update-button', $current_path);

$isAuditDashboardActive  = isMenuActive('audit-andon', $current_path);
$isAuditUnitMasterActive = isMenuActive('audit-andon/master-button', $current_path);
$isAuditAssignmentActive = isMenuActive('audit-andon/penugasan', $current_path);
$isAuditExportActive     = isMenuActive('audit-andon/export', $current_path);
$isAuditGroupActive      = $isAuditDashboardActive
    || $isAuditUnitMasterActive
    || $isAuditAssignmentActive
    || $isAuditExportActive;

$isPendataanActive          = isMenuActive('pendataan', $current_path);
$isPendataanDashboard       = $current_path === 'pendataan';
$isPendataanMasterActive    = isMenuActive('pendataan/master', $current_path);
$isPendataanFactoryActive   = isMenuActive('pendataan/master/factory', $current_path);
$isPendataanAreaActive      = isMenuActive('pendataan/master/area', $current_path);
$isPendataanKategoriActive  = isMenuActive('pendataan/master/kategori', $current_path);
$isPendataanSatuanActive    = isMenuActive('pendataan/master/satuan', $current_path);
$isPendataanBarangActive    = isMenuActive('pendataan/master/barang', $current_path);
$isPendataanKebutuhanActive = isMenuActive('pendataan/master/kebutuhan-area', $current_path);
$isPendataanTransaksiActive = isMenuActive('pendataan/transaksi', $current_path);
$isPendataanSaldoActive     = isMenuActive('pendataan/saldo', $current_path);
$isPendataanLaporanActive   = isMenuActive('pendataan/laporan', $current_path);

$isUserManagementActive = isMenuActive('users', $current_path);
?>

<div class="sidebar-brand">
    <a href="<?= base_url('/') ?>" class="brand-link">
        <img
            src="<?= base_url('assets/dist/assets/img/logoAndon.png') ?>"
            alt="Andon Logo"
            class="brand-image opacity-75 shadow"
        />
        <span class="brand-text fw-light">ANDON</span>
    </a>
</div>

<div class="sidebar-wrapper">
    <nav class="mt-2" aria-label="Main navigation">
        <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            data-accordion="false"
            id="navigation"
        >
            <!-- KELOMPOK HALAMAN ANDON -->
            <li class="nav-item<?= $isDashboardActive || $isButtonIdActive ? ' menu-open' : '' ?>">
                <a
                    href="#"
                    class="nav-link<?= $isDashboardActive || $isButtonIdActive ? ' active' : '' ?>"
                    data-bs-toggle="tooltip"
                    data-bs-placement="right"
                    title="Halaman Andon"
                >
                    <i class="nav-icon bi bi-display"></i>
                    <p>
                        Halaman Andon
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>

                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a
                            href="<?= base_url('/') ?>"
                            class="nav-link<?= $isDashboardActive ? ' active' : '' ?>"
                            title="Dashboard Andon"
                        >
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard Andon</p>
                        </a>
                    </li>

                    <?php if (\App\Libraries\Roles::has('admin')): ?>
                        <li class="nav-item">
                            <a
                                href="<?= base_url('andon/update-button') ?>"
                                class="nav-link<?= $isButtonIdActive ? ' active' : '' ?>"
                                title="Button ID"
                            >
                                <i class="nav-icon bi bi-upc-scan"></i>
                                <p>Button ID</p>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>

            <!-- KELOMPOK AUDIT ANDON -->
            <?php if (\App\Libraries\Roles::hasAny(['admin', 'pic_audit'])): ?>
                <li class="nav-item<?= $isAuditGroupActive ? ' menu-open' : '' ?>">
                    <a
                        href="#"
                        class="nav-link<?= $isAuditGroupActive ? ' active' : '' ?>"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="Audit Andon"
                    >
                        <i class="nav-icon bi bi-clipboard-check"></i>
                        <p>
                            Audit Andon
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a
                                href="<?= base_url('audit-andon') ?>"
                                class="nav-link<?= $isAuditDashboardActive && !$isAuditUnitMasterActive && !$isAuditAssignmentActive && !$isAuditExportActive ? ' active' : '' ?>"
                                title="Audit Button Andon"
                            >
                                <i class="nav-icon bi bi-clipboard2-check"></i>
                                <p>Audit Button Andon</p>
                            </a>
                        </li>

                        <?php if (\App\Libraries\Roles::has('admin')): ?>
                            <li class="nav-item">
                                <a
                                    href="<?= base_url('audit-andon/master-button') ?>"
                                    class="nav-link<?= $isAuditUnitMasterActive ? ' active' : '' ?>"
                                    title="Master Button Audit"
                                >
                                    <i class="nav-icon bi bi-ui-checks-grid"></i>
                                    <p>Master Button Audit</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a
                                    href="<?= base_url('audit-andon/penugasan') ?>"
                                    class="nav-link<?= $isAuditAssignmentActive ? ' active' : '' ?>"
                                    title="Penugasan IE LINE"
                                >
                                    <i class="nav-icon bi bi-people"></i>
                                    <p>Penugasan IE LINE</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a
                                    href="<?= base_url('audit-andon/export') ?>"
                                    class="nav-link<?= $isAuditExportActive ? ' active' : '' ?>"
                                    title="Export Audit Excel"
                                >
                                    <i class="nav-icon bi bi-file-earmark-excel"></i>
                                    <p>Export Audit Excel</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- KELOMPOK PENDATAAN BARANG -->
            <?php if (\App\Libraries\Roles::hasAny(['admin', 'petugas_area', 'viewer'])): ?>
                <li class="nav-item<?= $isPendataanActive ? ' menu-open' : '' ?>">
                    <a
                        href="<?= base_url('pendataan') ?>"
                        class="nav-link<?= $isPendataanActive ? ' active' : '' ?>"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="Pendataan Barang"
                    >
                        <i class="nav-icon bi bi-boxes"></i>
                        <p>
                            Pendataan Barang
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a
                                href="<?= base_url('pendataan') ?>"
                                class="nav-link<?= $isPendataanDashboard ? ' active' : '' ?>"
                                title="Dashboard Pendataan"
                            >
                                <i class="nav-icon bi bi-house-door"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item<?= $isPendataanMasterActive ? ' menu-open' : '' ?>">
                            <a
                                href="#"
                                class="nav-link<?= $isPendataanMasterActive ? ' active' : '' ?>"
                                title="Master Data"
                            >
                                <i class="nav-icon bi bi-database"></i>
                                <p>
                                    Master Data
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('pendataan/master/factory') ?>" class="nav-link<?= $isPendataanFactoryActive ? ' active' : '' ?>" title="Master Factory">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Factory</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= base_url('pendataan/master/area') ?>" class="nav-link<?= $isPendataanAreaActive ? ' active' : '' ?>" title="Master Area">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Area</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= base_url('pendataan/master/kategori') ?>" class="nav-link<?= $isPendataanKategoriActive ? ' active' : '' ?>" title="Master Kategori Barang">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Kategori Barang</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= base_url('pendataan/master/satuan') ?>" class="nav-link<?= $isPendataanSatuanActive ? ' active' : '' ?>" title="Master Satuan">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Satuan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= base_url('pendataan/master/barang') ?>" class="nav-link<?= $isPendataanBarangActive ? ' active' : '' ?>" title="Master Barang">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Barang</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= base_url('pendataan/master/kebutuhan-area') ?>" class="nav-link<?= $isPendataanKebutuhanActive ? ' active' : '' ?>" title="Kebutuhan Area">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Kebutuhan Area</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?= base_url('pendataan/transaksi') ?>" class="nav-link<?= $isPendataanTransaksiActive ? ' active' : '' ?>" title="Transaksi Barang">
                                <i class="nav-icon bi bi-arrow-left-right"></i>
                                <p>Transaksi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('pendataan/saldo') ?>" class="nav-link<?= $isPendataanSaldoActive ? ' active' : '' ?>" title="Saldo Barang">
                                <i class="nav-icon bi bi-box-seam"></i>
                                <p>Saldo Barang</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('pendataan/laporan') ?>" class="nav-link<?= $isPendataanLaporanActive ? ' active' : '' ?>" title="Laporan Barang">
                                <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                                <p>Laporan</p>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- KELOMPOK SETTING USER -->
            <?php if (\App\Libraries\Roles::has('admin')): ?>
                <li class="nav-item<?= $isUserManagementActive ? ' menu-open' : '' ?>">
                    <a
                        href="#"
                        class="nav-link<?= $isUserManagementActive ? ' active' : '' ?>"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="Setting User"
                    >
                        <i class="nav-icon bi bi-gear-fill"></i>
                        <p>
                            Setting User
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a
                                href="<?= base_url('users') ?>"
                                class="nav-link<?= $isUserManagementActive ? ' active' : '' ?>"
                                title="Manajemen User"
                            >
                                <i class="nav-icon bi bi-people"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<style>
.sidebar-menu .nav-link {
    position: relative;
    border-radius: 8px;
    margin: 2px 8px;
    transition: background-color .15s ease, color .15s ease;
}

.sidebar-menu .nav-link:hover {
    background-color: rgba(37, 211, 102, 0.08);
    color: #075E54;
}

.sidebar-menu .nav-link.active {
    background-color: rgba(37, 211, 102, 0.15);
    color: #075E54;
    font-weight: 600;
}

.sidebar-menu .nav-link.active::before {
    content: "";
    position: absolute;
    left: -8px;
    top: 6px;
    bottom: 6px;
    width: 4px;
    border-radius: 0 4px 4px 0;
    background-color: #25D366;
}

.sidebar-menu .nav-link.active .nav-icon,
.sidebar-menu .nav-link.active .nav-arrow {
    color: #128C7E;
}

.sidebar-menu > .nav-item > .nav-link {
    font-weight: 600;
}

.sidebar-menu .nav-treeview .nav-link {
    padding-left: 2.5rem;
}

.sidebar-menu .nav-treeview .nav-treeview .nav-link {
    padding-left: 3.25rem;
}

.tooltip .tooltip-inner {
    background-color: #111b21;
    color: #e9edef;
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 6px;
}

.tooltip .tooltip-arrow::before {
    border-right-color: #111b21 !important;
    border-left-color: #111b21 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipEls = document.querySelectorAll('#navigation [data-bs-toggle="tooltip"]');

    tooltipEls.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
