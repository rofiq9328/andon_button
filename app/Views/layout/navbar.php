<div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a
                class="nav-link"
                data-lte-toggle="sidebar"
                href="#"
                role="button"
                aria-label="Toggle sidebar"
              >
                <i class="bi bi-list"></i>
              </a>
            </li>
          </ul>
          <!--end::Start Navbar Links-->

          <!--begin::Navbar Search-->

          <!--end::Navbar Search-->

          <!--begin::End Navbar Links-->
          <?php
              $fullName = session()->get('full_name') ?? session()->get('username') ?? 'User';
              $roleRaw  = session()->get('role') ?? '-';
              $role     = strtolower(trim((string) $roleRaw)) === 'pic_audit' ? 'IE LINE' : $roleRaw;
              $initial  = strtoupper(substr($fullName, 0, 1));
          ?>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                <span
                  class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white fw-semibold me-2"
                  style="width: 32px; height: 32px; font-size: 0.85rem;"
                >
                  <?= esc($initial) ?>
                </span>
                <span class="d-none d-md-inline"><?= esc($fullName) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 overflow-hidden">
                <!--begin::User Header-->
                <li class="user-header text-bg-success text-center py-4">
                  <span
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-success fw-bold mb-2"
                    style="width: 64px; height: 64px; font-size: 1.5rem;"
                  >
                    <?= esc($initial) ?>
                  </span>
                  <p class="mb-1 fw-semibold"><?= esc($fullName) ?></p>
                  <span class="badge bg-white text-success text-uppercase"><?= esc($role) ?></span>
                </li>
                <!--end::User Header-->
                <!--begin::Menu Footer-->
                <li class="user-footer d-flex justify-content-center align-items-center p-2">
                  <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign out
                  </a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>