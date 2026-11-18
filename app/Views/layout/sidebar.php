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
$isSettingsActive      = $isMasterButtonActive || $isUserManagementActive;
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
              <?php if (in_array(session()->get('role'), ['admin', 'pic_audit'], true)): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('audit-andon') ?>" class="nav-link" title="Audit Andon">
                            <i class="nav-icon bi bi-clipboard-check"></i>
                            <p>Audit Andon</p>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (session()->get('role') === 'admin'): ?>
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