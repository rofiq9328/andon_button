<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<?= $this->include('audit/_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$totals = ['total' => 0, 'ok' => 0, 'bad' => 0, 'partial' => 0, 'empty' => 0];
foreach ($lines as $l) {
    foreach (array_keys($totals) as $k) {
        $totals[$k] += $l[$k];
    }
}
$belum   = $totals['partial'] + $totals['empty'];
$pctOk   = $totals['total'] > 0 ? $totals['ok'] / $totals['total'] * 100 : 0;
$tiles   = [
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
                <h1 class="mb-0 fs-3">DAFTAR LINE</h1>
                <div class="text-secondary small">
                    PIC: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?> ·
                    Status: <span class="fw-semibold"><?= esc($session['status']) ?></span>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <?php if ($isAdmin): ?>
                    <a href="<?= base_url('audit-andon') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> DAFTAR SESI
                    </a>
                <?php endif; ?>

                <?php if ($editable): ?>
                    <a href="<?= base_url($base . '/review') ?>" class="btn btn-primary btn-sm fw-bold">
                        <i class="fa-solid fa-clipboard-check me-1"></i> TINJAU &amp; SERAHKAN
                    </a>
                <?php elseif ($isAdmin): ?>
                    <form method="post" action="<?= base_url('audit-andon/buka/' . (int) $session['id']) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold">
                            <i class="fa-solid fa-lock-open me-1"></i> BUKA KEMBALI
                        </button>
                    </form>
                <?php endif; ?>
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

        <?php if ($editable && (int) date('j') >= 25 && $session['status'] === 'draft'): ?>
            <div class="alert alert-warning shadow-sm">Sudah tanggal 25. Silakan tinjau dan serahkan data bulan ini.</div>
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

        <div class="card audit-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between small mb-2">
                    <span class="fw-semibold">Progres keseluruhan</span>
                    <span class="text-secondary"><?= (int) round($pctOk) ?>% unit lengkap</span>
                </div>
                <div class="progress-stack">
                    <div class="progress-bar bg-success" style="width: <?= $totals['total'] ? $totals['ok'] / $totals['total'] * 100 : 0 ?>%"></div>
                    <div class="progress-bar bg-danger" style="width: <?= $totals['total'] ? $totals['bad'] / $totals['total'] * 100 : 0 ?>%"></div>
                    <div class="progress-bar bg-warning" style="width: <?= $totals['total'] ? $totals['partial'] / $totals['total'] * 100 : 0 ?>%"></div>
                </div>
            </div>
        </div>

        <?php if (empty($lines)): ?>
            <div class="alert alert-info">Belum ada area yang ditugaskan.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($lines as $l): ?>
                    <?php
                    if ($l['bad'] > 0) {
                        $tone = 'bad';       $label = 'Perlu perhatian'; $badge = 'danger';
                    } elseif ($l['total'] > 0 && $l['ok'] === $l['total']) {
                        $tone = 'ok';        $label = 'Lengkap';         $badge = 'success';
                    } elseif ($l['ok'] + $l['partial'] > 0) {
                        $tone = 'partial';   $label = 'Sebagian';        $badge = 'warning';
                    } else {
                        $tone = 'empty';     $label = 'Belum diisi';     $badge = 'secondary';
                    }
                    $pOk  = $l['total'] > 0 ? $l['ok'] / $l['total'] * 100 : 0;
                    $pBad = $l['total'] > 0 ? $l['bad'] / $l['total'] * 100 : 0;
                    $pPar = $l['total'] > 0 ? $l['partial'] / $l['total'] * 100 : 0;
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <a href="<?= base_url($base . '/line/' . $l['line']) ?>" class="text-decoration-none">
                            <div class="card line-tile tone-<?= $tone ?> shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <div class="line-title">LINE <?= $l['line'] ?></div>
                                        <span class="badge text-bg-<?= $badge ?>"><?= $label ?></span>
                                    </div>
                                    <div class="small text-secondary mb-2">
                                        <?= $l['ok'] ?> lengkap dari <?= $l['total'] ?> unit
                                    </div>
                                    <div class="progress-stack">
                                        <div class="progress-bar bg-success" style="width: <?= $pOk ?>%"></div>
                                        <div class="progress-bar bg-danger" style="width: <?= $pBad ?>%"></div>
                                        <div class="progress-bar bg-warning" style="width: <?= $pPar ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$editable): ?>
            <div class="alert alert-secondary mt-3 shadow-sm">
                Sesi ini terkunci. Data hanya bisa dilihat.
            </div>
        <?php endif; ?>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>
