<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">PENUGASAN PIC AUDIT</h1>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= base_url('audit-andon') ?>" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> AUDIT ANDON
                </a>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title fw-bold text-dark mb-0">Tambah Penugasan</h3>
            </div>
            <div class="card-body">
                <?php if (empty($pics)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada user dengan role PIC Audit. Buat dulu lewat menu User Management.
                    </div>
                <?php else: ?>
                    <form method="post" action="<?= base_url('audit-andon/penugasan/simpan') ?>" class="row g-2 align-items-end">
                        <?= csrf_field() ?>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">PIC</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Pilih PIC</option>
                                <?php foreach ($pics as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>">
                                        <?= esc($p['full_name'] ?: $p['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Line dari</label>
                            <input type="number" name="line_from" min="1" max="77" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Sampai line</label>
                            <input type="number" name="line_to" min="1" max="77" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">
                                <i class="fa-solid fa-plus me-1"></i> TAMBAH
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title fw-bold text-dark mb-0">Daftar Penugasan</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>PIC</th>
                                <th>Rentang Line</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pics)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">Belum ada PIC Audit.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($pics as $p): ?>
                                <?php $rows = $ranges[(int) $p['id']] ?? []; ?>
                                <?php $nama = esc($p['full_name'] ?: $p['username']); ?>

                                <?php if (empty($rows)): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= $nama ?></td>
                                        <td class="text-secondary">Belum ada penugasan</td>
                                        <td></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= $nama ?></td>
                                            <td>
                                                <span class="badge text-bg-info">
                                                    LINE <?= (int) $r['line_from'] ?> – <?= (int) $r['line_to'] ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <form method="post"
                                                      action="<?= base_url('audit-andon/penugasan/hapus/' . (int) $r['id']) ?>"
                                                      onsubmit="return confirm('Hapus penugasan ini?')"
                                                      class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-outline-danger btn-sm fw-bold shadow-sm">
                                                        <i class="fa-solid fa-trash-can me-1"></i> HAPUS
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="small text-secondary mt-3">
            Mengubah penugasan tidak mengubah sesi yang sudah dibuat.
            Unit dari penugasan baru akan ikut masuk saat PIC membuka form berikutnya.
        </div>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>
