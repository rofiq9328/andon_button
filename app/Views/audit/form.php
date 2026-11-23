<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<?= $this->include('audit/_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Daftar line hanya menampilkan jumlah button dan jumlah yang sudah diaudit.
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
                    <a href="<?= base_url($base . '/review') ?>" class="btn btn-success btn-sm fw-bold">
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

        <?php if (empty($lines)): ?>
            <div class="alert alert-info">Belum ada area yang ditugaskan.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($lines as $l): ?>
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <a href="<?= base_url($base . '/line/' . $l['line']) ?>" class="text-decoration-none">
                            <div class="card line-tile shadow-sm h-100">
                                <div class="card-body">
                                    <div class="line-title mb-3">LINE <?= (int) $l['line'] ?></div>
                                    <div class="d-flex justify-content-between align-items-center small mb-2">
                                        <span class="text-secondary">Jumlah Button</span>
                                        <strong class="text-dark"><?= (int) $l['total'] ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small">
                                        <span class="text-secondary">Sudah Diaudit</span>
                                        <strong class="text-success"><?= (int) $l['audited'] ?></strong>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>
