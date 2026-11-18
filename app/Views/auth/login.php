<!doctype html>
<html lang="id" data-bs-theme="light">
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
    <meta name="title" content="Login | ANDON BUTTON" />
    <meta name="author" content="ANDON BUTTON" />
    <meta name="description" content="Halaman login ANDON BUTTON" />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <link rel="preload" href="<?= base_url('assets/dist/css/adminlte.css') ?>" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
        rel="stylesheet"
        href="<?= base_url('assets/dist/plugins/fontsource-source-sans-3/index.css') ?>"
        media="print"
        onload="this.media = 'all'" />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="<?= base_url('assets/dist/plugins/overlayscrollbars/overlayscrollbars.min.css') ?>">
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->

    <link rel="stylesheet" href="<?= base_url('assets/dist/plugins/bootstrap_icons/bootstrap-icons.min.css') ?>" />

    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="<?= base_url('assets/dist/css/adminlte.css') ?>" />
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::Tema warna success + logo, disesuaikan untuk Andon System-->
    <style>
        .login-logo-wrapper {
            text-align: center;
            margin-bottom: 1rem;
        }

        .login-logo-img {
            width: 130px;
            height: 130px;
            object-fit: contain;
        }

        .login-logo-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--bs-success);
            text-decoration: none;
        }

        .login-page .btn-success {
            --bs-btn-bg: var(--bs-success);
            --bs-btn-border-color: var(--bs-success);
            --bs-btn-hover-bg: #157347;
            --bs-btn-hover-border-color: #146c43;
        }

        .login-page .form-control:focus {
            border-color: var(--bs-success);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
    </style>
    <!--end::Tema warna success + logo-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="login-page bg-body-secondary">
    <main class="login-box">
        <div class="login-logo-wrapper">
            <img src="<?= base_url('assets/dist/assets/img/andonLogo1.png') ?>" alt="Andon System" class="login-logo-img" />
            <a href="<?= base_url() ?>" class="login-logo-text">Andon Button</a>
        </div>
        <!-- /.login-logo-wrapper -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger py-2 mb-3">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="post">
                    <?= csrf_field() ?>

                    <label class="visually-hidden" for="loginUsername">Username</label>
                    <div class="input-group mb-3">
                        <input
                            id="loginUsername"
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Username"
                            value="<?= esc(old('username')) ?>"
                            required
                            autofocus />
                        <div class="input-group-text">
                            <span class="bi bi-person-fill"></span>
                        </div>
                    </div>
                    <label class="visually-hidden" for="loginPassword">Password</label>
                    <div class="input-group mb-3">
                        <input
                            id="loginPassword"
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Password"
                            required />
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                    </div>
                    <!--begin::Row-->
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success px-5">Sign In</button>
                    </div>
                    <!--end::Row-->
                </form>
            </div>
            <!-- /.login-card-body -->
        </div>

        <div class="text-center text-muted fixed-bottom mb-2 p-3">
            <strong>
                Copyright &copy; 2026&nbsp;
                <a href="https://IE/NOS DEPT." class="text-decoration-none">IE/NOS DEPT</a>.
            </strong>
            All rights reserved.
        </div>

    </main>
    <!-- /.login-box -->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="<?= base_url('assets/dist/plugins/overlayscrollbars/overlayscrollbars.browser.es6.min.js') ?>"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
        src="<?= base_url('assets/dist/plugins/popperjs/popper.min.js') ?>"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
        src="<?= base_url('assets/dist/plugins/bootstrap/bootstrap.min.js') ?>"></script>
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

    <!--end::Script-->
</body>
<!--end::Body-->

</html>