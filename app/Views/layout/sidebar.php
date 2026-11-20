<?php

$request_uri  = strtok($_SERVER['REQUEST_URI'], '?');      // buang query string (?foo=bar)
$request_uri  = str_replace('index.php', '', $request_uri); // buang index.php kalau belum pakai .htaccess
$request_uri  = preg_replace('#/+#', '/', $request_uri);    // rapikan slash ganda
$base_path    = trim(parse_url(base_url(), PHP_URL_PATH), '/'); // path folder project, mis. "andon"
$current_path = trim($request_uri, '/');

if ($base_path !== '' && strpos($current_path, $base_path) === 0) {
    $current_path = trim(substr($current_path, strlen($base_path)), '/');
}

// --- DEBUG: aktifkan sementara kalau menu masih belum tertandai ---
// echo '<pre style="color:#fff;background:#000;padding:8px;">REQUEST_URI: ' . $_SERVER['REQUEST_URI']
//    . "\nbase_path: " . $base_path
//    . "\ncurrent_path: " . $current_path . '</pre>';

function isMenuActive($path, $current_path)
{
    $path = trim($path, '/');
    if ($path === '') {
        return $current_path === '';
    }
    return $current_path === $path || strpos($current_path, $path . '/') === 0;
}

$isDashboardActive     = isMenuActive('', $current_path);
$isMasterButtonActive  = isMenuActive('andon/update-button', $current_path);
$isUserManagementActive = isMenuActive('users', $current_path);
$isAuditActive             = isMenuActive('audit-andon', $current_path);
$isPendataanActive        = isMenuActive('pendataan', $current_path);
$isPendataanDashboard     = $current_path === 'pendataan';
$isPendataanMasterActive  = isMenuActive('pendataan/master', $current_path);
$isPendataanFactoryActive = isMenuActive('pendataan/master/factory', $current_path);
$isPendataanAreaActive    = isMenuActive('pendataan/master/area', $current_path);
$isPendataanKategoriActive = isMenuActive('pendataan/master/kategori', $current_path);
$isPendataanSatuanActive = isMenuActive('pendataan/master/satuan', $current_path);
$isPendataanBarangActive = isMenuActive('pendataan/master/barang', $current_path);
$isPendataanKebutuhanActive = isMenuActive('pendataan/master/kebutuhan-area', $current_path);
$isPendataanTransaksiActive = isMenuActive('pendataan/transaksi', $current_path);
$isSettingsActive         = $isMasterButtonActive || $isUserManagementActive;
?>
<div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="<?= base_url('/') ?>" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="<?= base_url('assets/dist/assets/img/logoAndon.png') ?>"
              alt="Andon Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">ANDON</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2" aria-label="Main navigation">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-item">
                <a
                  href="<?= base_url('/') ?>"
                  class="nav-link<?= $isDashboardActive ? ' active' : '' ?>"
                  data-bs-toggle="tooltip"
                  data-bs-placement="right"
                  title="Dashboard"
                >
                  <i class="nav-icon bi bi-house"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <?php if (\App\Libraries\Roles::hasAny(['admin', 'pic_audit'])): ?>
                <li class="nav-item">
                  <a
                    href="<?= base_url('audit-andon') ?>"
                    class="nav-link<?= $isAuditActive ? ' active' : '' ?>"
                    data-bs-toggle="tooltip"
                    data-bs-placement="right"
                    title="Audit Andon"
                  >
                    <i class="nav-icon bi bi-clipboard-check"></i>
                    <p>Audit Andon</p>
                  </a>
                </li>
              <?php endif; ?>
              <?php if (\App\Libraries\Roles::hasAny(['admin', 'petugas_area', 'verifikator', 'viewer'])): ?>
                <!-- Menu Pendataan Barang -->
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
                          <a
                            href="<?= base_url('pendataan/master/factory') ?>"
                            class="nav-link<?= $isPendataanFactoryActive ? ' active' : '' ?>"
                            title="Master Factory"
                          >
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Factory</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a
                            href="<?= base_url('pendataan/master/area') ?>"
                            class="nav-link<?= $isPendataanAreaActive ? ' active' : '' ?>"
                            title="Master Area"
                          >
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Area</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a
                            href="<?= base_url('pendataan/master/kategori') ?>"
                            class="nav-link<?= $isPendataanKategoriActive ? ' active' : '' ?>"
                            title="Master Kategori Barang"
                          >
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Kategori Barang</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a
                            href="<?= base_url('pendataan/master/satuan') ?>"
                            class="nav-link<?= $isPendataanSatuanActive ? ' active' : '' ?>"
                            title="Master Satuan"
                          >
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Satuan</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a
                            href="<?= base_url('pendataan/master/barang') ?>"
                            class="nav-link<?= $isPendataanBarangActive ? ' active' : '' ?>"
                            title="Master Barang"
                          >
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Barang</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a
                            href="<?= base_url('pendataan/master/kebutuhan-area') ?>"
                            class="nav-link<?= $isPendataanKebutuhanActive ? ' active' : '' ?>"
                            title="Kebutuhan Area"
                          >
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Kebutuhan Area</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <li class="nav-item">
                      <a href="<?= base_url('pendataan/transaksi') ?>"
                         class="nav-link<?= $isPendataanTransaksiActive ? ' active' : '' ?>"
                         title="Transaksi Barang">
                        <i class="nav-icon bi bi-arrow-left-right"></i>
                        <p>Transaksi</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="#" class="nav-link disabled" aria-disabled="true" tabindex="-1"
                         title="Modul Saldo Barang akan dibuat setelah Transaksi selesai">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>Saldo Barang <span class="badge text-bg-secondary ms-2">Segera</span></p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="#" class="nav-link disabled" aria-disabled="true" tabindex="-1"
                         title="Modul Laporan akan dibuat setelah Transaksi selesai">
                        <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                        <p>Laporan <span class="badge text-bg-secondary ms-2">Segera</span></p>
                      </a>
                    </li>
                  </ul>
                </li>
              <?php endif; ?>
                <?php if (\App\Libraries\Roles::has('admin')): ?>
              <li class="nav-item<?= $isSettingsActive ? ' menu-open' : '' ?>">
                <a
                  href="#"
                  class="nav-link<?= $isSettingsActive ? ' active' : '' ?>"
                  data-bs-toggle="tooltip"
                  data-bs-placement="right"
                  title="Settings"
                >
                  <i class="nav-icon bi bi-gear-fill"></i>
                  <p>
                    Settings
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a
                      href="<?= base_url('andon/update-button') ?>"
                      class="nav-link<?= $isMasterButtonActive ? ' active' : '' ?>"
                      data-bs-toggle="tooltip"
                      data-bs-placement="right"
                      title="Master Button"
                    >
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Master Button</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a
                      href="<?= base_url('users') ?>"
                      class="nav-link<?= $isUserManagementActive ? ' active' : '' ?>"
                      data-bs-toggle="tooltip"
                      data-bs-placement="right"
                      title="User Management"
                    >
                      <i class="nav-icon bi bi-circle"></i>
                      <p>User Management</p>
                    </a>
                  </li>
                </ul>
              </li>
              <?php endif; ?>
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->

<style>
/* ================================================================
   Active state & hover ala WhatsApp Web
   ================================================================ */
.sidebar-menu .nav-link {
    position: relative;
    border-radius: 8px;
    margin: 2px 8px;
    transition: background-color .15s ease, color .15s ease;
}

/* Hover: sorotan lembut, mirip item chat WA Web saat disorot */
.sidebar-menu .nav-link:hover {
    background-color: rgba(37, 211, 102, 0.08);
    color: #075E54;
}

/* Aktif: garis indikator hijau di kiri + background lebih pekat */
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

/* Tooltip: gaya gelap membulat mirip WA Web */
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
// Aktifkan tooltip Bootstrap untuk setiap menu di sidebar
document.addEventListener('DOMContentLoaded', function () {
    var tooltipEls = document.querySelectorAll('#navigation [data-bs-toggle="tooltip"]');
    tooltipEls.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>