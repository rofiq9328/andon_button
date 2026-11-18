<!doctype html>
<html lang="en" data-lte-color-mode="off" data-bs-theme="light">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>ANDON BUTTON</title>

  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light" />
  <meta name="theme-color" content="#198754" />
  <!--end::Accessibility Meta Tags-->

  <!--begin::Primary Meta Tags-->
  <meta name="title" content="Andon System" />
  <meta name="author" content="Andon System" />
  <meta name="description" content="Sistem monitoring dan manajemen Andon Button." />
  <!--end::Primary Meta Tags-->

  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <link rel="preload" href="<?= base_url('assets/dist/css/adminlte.css') ?>" as="style" />
  <link rel="stylesheet" href="<?= base_url('assets/dist/css/adminlte-colors.css') ?>" />
  <!--end::Accessibility Features-->

  <!--begin::Fonts-->
  <link
  rel="stylesheet"
  href="<?= base_url('assets/dist/plugins/fontsource-source-sans-3/index.css') ?>"
  media="print"
  onload="this.media = 'all'"
/>
  <!--end::Fonts-->

  <!--begin::Third Party Plugin(OverlayScrollbars)--> 
  <link rel="stylesheet" href="<?= base_url('assets/dist/plugins/overlayscrollbars/overlayscrollbars.min.css') ?>">
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link rel="stylesheet" href="<?= base_url('assets/dist/plugins/bootstrap_icons/bootstrap-icons.min.css') ?>"/>
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <link rel="stylesheet" href="<?= base_url('assets/dist/plugins/tabulator/tabulator_bootstrap5.min.css') ?>"/>

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="<?= base_url('assets/dist/css/adminlte.css') ?>" />
  <!--end::Required Plugin(AdminLTE)-->

  <link rel="stylesheet" href="<?= base_url('assets/dist/plugins/fontawesome-free/css/all.min.css') ?>">
  
  <script>
    const BASE_URL = "<?= rtrim(base_url(), '/') ?>/";
  </script>

  <!-- Render custom styles dari view -->
  <?= $this->renderSection('styles') ?>
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg sidebar-mini sidebar-without-hover sidebar-collapse bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">
    <!--begin::Header-->
    <nav class="app-header navbar navbar-expand bg-body" data-bs-theme="light">
      <!--begin::Container-->
      <?= $this->include('layout/navbar') ?>
      <!--end::Container-->
    </nav>
    <!--end::Header-->
    <!--begin::Sidebar-->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <!--begin::Sidebar Brand-->
      <?= $this->include('layout/sidebar') ?>
      <!--end::Sidebar Wrapper-->
    </aside>
    <!--end::Sidebar-->
    <!--begin::App Main-->
    <main class="app-main">
      <!--begin::App Content Header-->
      <?= $this->renderSection('content') ?>
      <!--end::App Content-->
    </main>
    <!--end::App Main-->
    <!--begin::Footer-->
    <?= $this->include('layout/footer') ?>
    <!--end::Footer-->
  </div>
  <!--end::App Wrapper-->
  <!--begin::Script-->
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script src="<?= base_url('assets/dist/plugins/overlayscrollbars/overlayscrollbars.browser.es6.min.js') ?>"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script
    src="<?= base_url('assets/dist/plugins/popperjs/popper.min.js') ?>"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
  <script
    src="<?= base_url('assets/dist/plugins/bootstrap/bootstrap.min.js') ?>"></script>
  
  <script src="<?= base_url('assets/dist/plugins/tabulator/tabulator.min.js') ?>"></script>

  <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
  <script src="<?= base_url('assets/dist/js/adminlte.js') ?>"></script>
  <!--end::Required Plugin(AdminLTE)-->
  <!--begin::OverlayScrollbars Configure-->
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

      // Disable OverlayScrollbars on mobile devices to prevent touch interference
      const isMobile = window.innerWidth <= 992;

      if (
        sidebarWrapper &&
        OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
        !isMobile
      ) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });
  </script>
  <!--end::OverlayScrollbars Configure-->

  <script src="<?= base_url('assets/dist/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>

  <!--begin::Global Toast Helper-->
  <script>
    /**
     * Tampilkan toast Bootstrap 5 di pojok kanan atas.
     * @param {'success'|'error'|'warning'|'info'} type
     * @param {string} title
     * @param {string} message - boleh mengandung HTML (mis. <ul> daftar error)
     */
    function showBootstrapToast(type, title, message) {
      const bgMap = {
        success: 'success',
        error: 'danger',
        warning: 'warning',
        info: 'primary',
      };
      const bg = bgMap[type] || 'secondary';

      let container = document.getElementById('toast-container');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = 1100;
        document.body.appendChild(container);
      }

      const toastEl = document.createElement('div');
      toastEl.className = `toast align-items-center text-bg-${bg} border-0`;
      toastEl.setAttribute('role', 'alert');
      toastEl.setAttribute('aria-live', 'assertive');
      toastEl.setAttribute('aria-atomic', 'true');
      toastEl.innerHTML = `
          <div class="d-flex">
            <div class="toast-body">
              <strong>${title}</strong><br>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        `;

      container.appendChild(toastEl);
      const toast = new bootstrap.Toast(toastEl, {
        delay: 6000
      });
      toast.show();

      toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
  </script>
  <!--end::Global Toast Helper-->

  <!--end::Script-->
  <?= $this->renderSection('scripts') ?>
</body>
<!--end::Body-->

</html>