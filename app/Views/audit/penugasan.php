<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0">Penugasan PIC Audit</h1>
        <a href="<?= base_url('audit-andon') ?>" class="btn btn-sm btn-outline-secondary">Kembali ke Audit Andon</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header fw-bold">Tambah penugasan</div>
        <div class="card-body">
            <?php if (empty($pics)): ?>
                <div class="alert alert-info mb-0">
                    Belum ada user dengan role PIC Audit. Buat dulu lewat menu User Management.
                </div>
            <?php else: ?>
                <form method="post" action="<?= base_url('audit-andon/penugasan/simpan') ?>" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-md-4">
                        <label class="form-label">PIC</label>
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
                        <label class="form-label">Line dari</label>
                        <input type="number" name="line_from" min="1" max="77" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sampai line</label>
                        <input type="number" name="line_to" min="1" max="77" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tambah</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>PIC</th>
                        <th>Rentang line</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pics)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">Belum ada PIC Audit.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($pics as $p): ?>
                        <?php $rows = $ranges[(int) $p['id']] ?? []; ?>
                        <?php $nama = esc($p['full_name'] ?: $p['username']); ?>

                        <?php if (empty($rows)): ?>
                            <tr>
                                <td><?= $nama ?></td>
                                <td class="text-muted">Belum ada penugasan</td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= $nama ?></td>
                                    <td>Line <?= (int) $r['line_from'] ?> sampai <?= (int) $r['line_to'] ?></td>
                                    <td>
                                        <form method="post"
                                              action="<?= base_url('audit-andon/penugasan/hapus/' . (int) $r['id']) ?>"
                                              onsubmit="return confirm('Hapus penugasan ini?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
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

    <div class="small text-muted mt-3">
        Mengubah penugasan tidak mengubah sesi yang sudah dibuat.
        Unit dari penugasan baru akan ikut masuk saat PIC membuka form berikutnya.
    </div>

</div>

<?= $this->endSection() ?>
