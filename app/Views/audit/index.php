<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">AUDIT ANDON <?= esc($periode) ?></h1>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <a href="<?= base_url('audit-andon/penugasan') ?>" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                    <i class="fa-solid fa-users me-1"></i> PENUGASAN PIC
                </a>
                <form method="get" action="<?= base_url('audit-andon') ?>" class="d-flex gap-2">
                    <input type="month" name="periode" value="<?= esc($periode) ?>" class="form-control form-control-sm">
                    <button type="submit" class="btn btn-success btn-sm fw-bold shadow-sm">TAMPILKAN</button>
                </form>
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

        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title fw-bold text-dark mb-0">Sesi PIC</h3>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>PIC</th>
                                        <th>Status</th>
                                        <th style="min-width: 180px;">Progres</th>
                                        <th>Diserahkan</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sessions)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-secondary py-4">
                                                Belum ada sesi audit untuk periode ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php foreach ($sessions as $s): ?>
                                        <?php $pct = $s['total'] > 0 ? (int) round($s['terisi'] / $s['total'] * 100) : 0; ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= esc($s['full_name'] ?? $s['username'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge text-bg-<?= $s['status'] === 'submitted' ? 'success' : 'secondary' ?>">
                                                    <?= strtoupper(esc($s['status'])) ?>
                                                </span>
                                                <?php if (!$s['editable']): ?>
                                                    <span class="badge text-bg-dark">TERKUNCI</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress progress-sm flex-grow-1">
                                                        <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                                                    </div>
                                                    <span class="small text-secondary text-nowrap">
                                                        <?= (int) $s['terisi'] ?> / <?= (int) $s['total'] ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="small text-secondary">
                                                <?= $s['submitted_at'] ? esc($s['submitted_at']) : '-' ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <a href="<?= base_url('audit-andon/sesi/' . (int) $s['id']) ?>"
                                                       class="btn btn-success btn-sm fw-bold shadow-sm">LIHAT</a>

                                                    <?php if (!$s['editable']): ?>
                                                        <form method="post" action="<?= base_url('audit-andon/buka/' . (int) $s['id']) ?>">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm">BUKA KEMBALI</button>
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
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>
