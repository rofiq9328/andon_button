<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    /* Ganti aksen warna default (biru) jadi success/hijau, senada dengan card-outline card-success */

    /* Fokus di kotak pencarian */
    #searchUser:focus {
        border-color: var(--bs-success);
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    /* Halaman pagination yang sedang aktif */
    .card-footer .pagination .page-item.active .page-link {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
    }

    /* Warna teks saat hover tombol pagination (selain yang aktif/disabled) */
    .card-footer .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
        color: var(--bs-success);
    }

    /* Outline fokus tombol pagination */
    .card-footer .pagination .page-link:focus {
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <div class="d-flex align-items-center gap-3">
                  <h1 class="mb-0 fs-3">USER MANAGEMENT</h1>
                  <button
                    type="button"
                    class="btn btn-success btn-sm shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-add-user"
                    title="New user"
                  >
                    <i class="fas fa-user-plus me-1"></i>
                    <span class="d-none d-sm-inline">New user</span>
                  </button>
                </div>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-12">
                <!--begin::Card-->
                <div class="card card-outline card-success shadow-sm">
                  <!--begin::Card Header-->
                  <div class="card-header bg-white d-flex justify-content-end">
                    <form method="get" action="<?= base_url('users') ?>" class="input-group input-group-sm" style="max-width: 260px;">
                      <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                      <input
                        type="text"
                        id="searchUser"
                        name="search"
                        value="<?= esc(old('search', $search ?? '')) ?>"
                        class="form-control"
                        placeholder="Cari user..."
                      />
                    </form>
                  </div>
                  <!--end::Card Header-->
                  <!--begin::Card Body-->
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle m-0">
                        <thead>
                          <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($users)): ?>
                            <tr>
                              <td colspan="6" class="text-center text-body-secondary py-4">
                                Tidak ada user yang cocok dengan pencarian/filter.
                              </td>
                            </tr>
                          <?php endif; ?>

                          <?php foreach ($users as $user): ?>
                            <?php
                                $initial   = strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1));
                                $roleBadge = [
                                    'admin'      => 'text-bg-danger',
                                    'user'       => 'text-bg-secondary',
                                ][$user['role']] ?? 'text-bg-secondary';
                            ?>
                            <tr>
                              <td>
                                <div class="d-flex align-items-center">
                                  <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white fw-semibold me-2"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;"
                                  >
                                    <?= esc($initial) ?>
                                  </span>
                                  <div>
                                    <div class="fw-medium"><?= esc($user['full_name'] ?: '-') ?></div>
                                    <div class="text-body-secondary fs-7">@<?= esc($user['username']) ?></div>
                                  </div>
                                </div>
                              </td>
                              <td><?= esc($user['email'] ?: '-') ?></td>
                              <td>
                                <span class="badge <?= $roleBadge ?> text-uppercase"><?= esc($user['role']) ?></span>
                              </td>
                              <td>
                                <?php if ($user['is_active']): ?>
                                  <span class="badge text-bg-success">Aktif</span>
                                <?php else: ?>
                                  <span class="badge text-bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                              </td>
                              <td><?= esc(date('d M Y', strtotime($user['created_at']))) ?></td>
                              <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                  <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-edit-user"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modal-edit-user"
                                    data-id="<?= esc($user['id']) ?>"
                                    data-username="<?= esc($user['username']) ?>"
                                    data-full-name="<?= esc($user['full_name']) ?>"
                                    data-email="<?= esc($user['email']) ?>"
                                    data-role="<?= esc($user['role']) ?>"
                                    data-roles="<?= esc(implode(',', $userRoles[(int) $user['id']] ?? [$user['role']])) ?>"
                                    data-active="<?= esc($user['is_active']) ?>"
                                    aria-label="Edit <?= esc($user['username']) ?>"
                                  >
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                  </button>
                                  <button
                                    type="button"
                                    class="btn btn-outline-danger btn-delete-user"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modal-delete-user"
                                    data-id="<?= esc($user['id']) ?>"
                                    data-username="<?= esc($user['username']) ?>"
                                    aria-label="Delete <?= esc($user['username']) ?>"
                                  >
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                  </button>
                                </div>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    <!-- /.table-responsive -->
                  </div>
                  <!--end::Card Body-->
                  <!--begin::Card Footer-->
                  <?php if (!empty($users)): ?>
                  <div class="card-footer clearfix">
                    <div class="float-start pt-1 fs-7 text-body-secondary">
                      Menampilkan <?= count($users) ?> dari <?= $pager->getTotal() ?> user
                    </div>
                    <div class="float-end">
                      <?= $pager->links() ?>
                    </div>
                  </div>
                  <?php endif; ?>
                  <!--end::Card Footer-->
                </div>
                <!--end::Card-->
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->

            <!--begin::Add User Modal-->
            <div
              class="modal fade"
              id="modal-add-user"
              tabindex="-1"
              aria-labelledby="modal-add-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="<?= base_url('users') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-success text-white">
                      <h5 class="modal-title" id="modal-add-user-label"><i class="fas fa-user-plus me-1"></i> Add new user</h5>
                      <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                      ></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label for="new-user-name" class="form-label">Full name</label>
                        <input
                          type="text"
                          name="full_name"
                          class="form-control"
                          id="new-user-name"
                          placeholder=""
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-user-username" class="form-label">Username</label>
                        <input
                          type="text"
                          name="username"
                          class="form-control"
                          id="new-user-username"
                          placeholder=""
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-user-email" class="form-label">Email (opsional)</label>
                        <input
                          type="email"
                          name="email"
                          class="form-control"
                          id="new-user-email"
                          placeholder="name@example.com"
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-user-password" class="form-label">Password</label>
                        <input
                          type="password"
                          name="password"
                          class="form-control"
                          id="new-user-password"
                          minlength="6"
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-user-role" class="form-label">Role</label>
                        <div class="d-flex flex-column gap-1 border rounded p-2" id="new-user-roles">
                          <?php foreach ($allRoles as $code => $label): ?>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" name="roles[]" value="<?= esc($code) ?>" id="new-user-role-<?= esc($code) ?>"<?= $code === 'user' && 'new-user' === 'new-user' ? ' checked' : '' ?>>
                              <label class="form-check-label" for="new-user-role-<?= esc($code) ?>"><?= esc($label) ?></label>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      </div>
                      <div class="form-check">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          name="is_active"
                          value="1"
                          id="new-user-active"
                          checked
                        />
                        <label class="form-check-label" for="new-user-active">
                          Aktifkan akun ini
                        </label>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                      </button>
                      <button type="submit" class="btn btn-success">Create user</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Add User Modal-->

            <!--begin::Edit User Modal (shared, diisi via JS per baris)-->
            <div
              class="modal fade"
              id="modal-edit-user"
              tabindex="-1"
              aria-labelledby="modal-edit-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="form-edit-user" action="" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="edit-user-id" value="" />
                    <div class="modal-header bg-warning">
                      <h5 class="modal-title" id="modal-edit-user-label"><i class="fas fa-edit me-1"></i> Edit user</h5>
                      <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                      ></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label for="edit-user-name" class="form-label">Full name</label>
                        <input type="text" name="full_name" class="form-control" id="edit-user-name" required />
                      </div>
                      <div class="mb-3">
                        <label for="edit-user-username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" id="edit-user-username" required />
                      </div>
                      <div class="mb-3">
                        <label for="edit-user-email" class="form-label">Email (opsional)</label>
                        <input type="email" name="email" class="form-control" id="edit-user-email" />
                      </div>
                      <div class="mb-3">
                        <label for="edit-user-password" class="form-label">Password baru</label>
                        <input type="password" name="password" class="form-control" id="edit-user-password" minlength="6" placeholder="Kosongkan jika tidak diganti" />
                      </div>
                      <div class="mb-3">
                        <label for="edit-user-role" class="form-label">Role</label>
                        <div class="d-flex flex-column gap-1 border rounded p-2" id="edit-user-roles">
                          <?php foreach ($allRoles as $code => $label): ?>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" name="roles[]" value="<?= esc($code) ?>" id="edit-user-role-<?= esc($code) ?>"<?= $code === 'user' && 'edit-user' === 'new-user' ? ' checked' : '' ?>>
                              <label class="form-check-label" for="edit-user-role-<?= esc($code) ?>"><?= esc($label) ?></label>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-user-active" />
                        <label class="form-check-label" for="edit-user-active">Aktifkan akun ini</label>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                      </button>
                      <button type="submit" class="btn btn-success">Save changes</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Edit User Modal-->

            <!--begin::Delete User Modal (shared, diisi via JS per baris)-->
            <div
              class="modal fade"
              id="modal-delete-user"
              tabindex="-1"
              aria-labelledby="modal-delete-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="form-delete-user" action="" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-danger text-white">
                      <h5 class="modal-title" id="modal-delete-user-label"><i class="fas fa-trash me-1"></i> Delete user</h5>
                      <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                      ></button>
                    </div>
                    <div class="modal-body">
                      <p class="mb-0">
                        Yakin ingin menghapus user <strong id="delete-user-username">ini</strong>?
                        Tindakan ini tidak bisa dibatalkan.
                      </p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                      </button>
                      <button type="submit" class="btn btn-danger">Delete user</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Delete User Modal-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Isi modal Edit & Delete berdasarkan tombol baris yang diklik
    document.addEventListener('DOMContentLoaded', function () {
        const editModal   = document.getElementById('modal-edit-user');
        const deleteModal = document.getElementById('modal-delete-user');

        editModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const form = document.getElementById('form-edit-user');

            form.action = BASE_URL + 'users/update/' + btn.dataset.id;
            document.getElementById('edit-user-id').value       = btn.dataset.id;
            document.getElementById('edit-user-name').value     = btn.dataset.fullName || '';
            document.getElementById('edit-user-username').value = btn.dataset.username || '';
            document.getElementById('edit-user-email').value    = btn.dataset.email || '';
            document.getElementById('edit-user-password').value = '';
            var dipilih = (btn.dataset.roles || btn.dataset.role || 'user').split(',');
            document.querySelectorAll('#edit-user-roles input[name="roles[]"]').forEach(function (cb) {
                cb.checked = dipilih.indexOf(cb.value) !== -1;
            });
            document.getElementById('edit-user-active').checked = btn.dataset.active === '1';
        });

        deleteModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const form = document.getElementById('form-delete-user');

            form.action = BASE_URL + 'users/delete/' + btn.dataset.id;
            document.getElementById('delete-user-username').textContent = btn.dataset.username || 'ini';
        });
    });

    // Toast untuk flashdata success/error, konsisten dengan halaman lain
    const flashSuccess = <?= json_encode(session()->getFlashdata('success')) ?>;
    const flashError   = <?= json_encode(session()->getFlashdata('error')) ?>;

    if (flashSuccess) {
        showBootstrapToast('success', 'Berhasil', flashSuccess);
    }
    if (flashError) {
        showBootstrapToast('error', 'Gagal', flashError);
    }
</script>
<?= $this->endSection() ?>