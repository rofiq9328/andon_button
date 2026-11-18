<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0">Audit Andon <?= esc($periode) ?></h1>

        <form method="get" action="<?= base_url('audit-andon') ?>" class="d-flex gap-2">
            <input type="month" name="periode" value="<?= esc($periode) ?>" class="form-control form-control-sm">
            <button type="submit" class="btn btn-sm btn-outline-primary">Tampilkan</button>
        </form>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>PIC</th>
                        <th>Status</th>
                        <th>Progres</th>
                        <th>Diserahkan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Belum ada sesi audit untuk periode ini.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td><?= esc($s['full_name'] ?? $s['username'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= $s['status'] === 'submitted' ? 'success' : 'secondary' ?>">
                                    <?= esc($s['status']) ?>
                                </span>
                                <?php if (!$s['editable']): ?>
                                    <span class="badge bg-dark">terkunci</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int) $s['terisi'] ?> / <?= (int) $s['total'] ?></td>
                            <td><?= $s['submitted_at'] ? esc($s['submitted_at']) : '-' ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-primary"
                                       href="<?= base_url('audit-andon/sesi/' . (int) $s['id']) ?>">Lihat</a>

                                    <?php if (!$s['editable']): ?>
                                        <form method="post" action="<?= base_url('audit-andon/buka/' . (int) $s['id']) ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-warning">Buka Kembali</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
