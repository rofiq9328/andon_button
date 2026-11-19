<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<?= $this->include('audit/_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$belum = $totals['partial'] + $totals['empty'];
$tiles = [
    ['Total unit', $totals['total'], 'neutral'],
    ['Lengkap', $totals['ok'], 'ok'],
    ['Perlu perhatian', $totals['bad'], 'bad'],
    ['Belum lengkap', $belum, 'partial'],
];
?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <div class="text-secondary small text-uppercase fw-semibold">Audit Andon <?= esc($session['periode']) ?></div>
                <h1 class="mb-0 fs-3">TINJAU SEBELUM DISERAHKAN</h1>
                <div class="text-secondary small">
                    PIC: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
                <a href="<?= base_url($base) ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> KEMBALI
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

        <div class="row g-3 mb-3">
            <?php foreach ($tiles as [$label, $n, $tone]): ?>
                <div class="col-6 col-lg-3">
                    <div class="stat-tile stat-<?= $tone ?>">
                        <div class="stat-label"><?= esc($label) ?></div>
                        <div class="stat-num"><?= (int) $n ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($belum > 0): ?>
            <div class="alert alert-warning shadow-sm">
                Masih ada <strong><?= $belum ?></strong> unit yang belum lengkap.
                Anda tetap bisa menyerahkan, tetapi pastikan kondisi tersebut memang sudah dicek.
            </div>
        <?php endif; ?>

        <div class="card audit-card mb-3">
            <div class="card-header fw-bold text-dark">Ringkasan per line</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Line</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Lengkap</th>
                                <th class="text-center">Perlu perhatian</th>
                                <th class="text-center">Belum lengkap</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($perLine as $l): ?>
                                <tr>
                                    <td class="fw-bold">LINE <?= $l['line'] ?></td>
                                    <td class="text-center"><?= $l['total'] ?></td>
                                    <td class="text-center text-success-emphasis fw-semibold"><?= $l['ok'] ?></td>
                                    <td class="text-center text-danger-emphasis fw-semibold"><?= $l['bad'] ?></td>
                                    <td class="text-center text-warning-emphasis fw-semibold"><?= $l['partial'] + $l['empty'] ?></td>
                                    <td class="text-end">
                                        <a href="<?= base_url($base . '/line/' . $l['line']) ?>"
                                           class="btn btn-outline-primary btn-sm fw-bold">BUKA</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card audit-card mb-3">
            <div class="card-header fw-bold text-dark">Unit perlu perhatian</div>
            <div class="card-body p-0">
                <?php if (empty($problems)): ?>
                    <div class="p-4 text-center text-secondary">Tidak ada unit yang perlu perhatian.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Line</th>
                                    <th>Unit</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($problems as $p): ?>
                                    <tr>
                                        <td class="fw-bold">LINE <?= $p['line'] ?></td>
                                        <td>#<?= $p['line_no'] ?></td>
                                        <td class="text-end">
                                            <a href="<?= base_url($base . '/line/' . $p['line']) . '#unit-' . $p['id'] ?>"
                                               class="btn btn-outline-danger btn-sm fw-bold">LIHAT</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card audit-card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <?php if ($editable): ?>
                    <div class="text-secondary small">
                        Setelah diserahkan, data masih bisa diubah sampai akhir bulan.
                    </div>
                    <form method="post" action="<?= base_url('audit-andon/simpan') ?>"
                          onsubmit="return confirm('Serahkan data bulan ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
                        <button type="submit" name="action" value="serahkan" class="btn btn-success fw-bold">
                            <i class="fa-solid fa-paper-plane me-1"></i> SERAHKAN DATA
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0 w-100">
                        Sesi ini terkunci. Hanya admin yang bisa membukanya kembali.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>
