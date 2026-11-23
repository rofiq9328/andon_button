<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<?= $this->include('audit/_styles') ?>
<style>
    .audit-line-link {
        display: block;
        height: 100%;
        color: inherit;
        text-decoration: none;
    }

    .audit-line-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        overflow: hidden;
        background: var(--bs-body-bg);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.045);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .audit-line-link:hover .audit-line-card {
        transform: translateY(-3px);
        border-color: rgba(var(--bs-success-rgb), 0.55);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.09);
    }

    .audit-line-icon {
        display: inline-flex;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
        font-size: 1.05rem;
    }

    .audit-line-caption {
        color: var(--bs-secondary-color);
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.09em;
    }

    .audit-line-title {
        color: var(--bs-body-color);
        font-size: 1.2rem;
        font-weight: 800;
        line-height: 1.25;
    }

    .audit-line-arrow {
        display: inline-flex;
        width: 30px;
        height: 30px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .audit-line-link:hover .audit-line-arrow {
        background: var(--bs-success);
        color: #fff;
    }

    .audit-line-stat {
        height: 100%;
        min-height: 82px;
        padding: 0.75rem;
        border-radius: 12px;
        background: var(--bs-tertiary-bg);
    }

    .audit-line-stat-done {
        background: var(--bs-success-bg-subtle);
    }

    .audit-line-stat-label {
        color: var(--bs-secondary-color);
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .audit-line-stat-done .audit-line-stat-label {
        color: var(--bs-success-text-emphasis);
    }

    .audit-line-stat-value {
        margin-top: 0.35rem;
        color: var(--bs-body-color);
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .audit-line-stat-done .audit-line-stat-value {
        color: var(--bs-success-text-emphasis);
    }
</style>
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
                <div class="text-secondary small text-uppercase fw-semibold">IE LINE · AUDIT ANDON <?= esc($session['periode']) ?></div>
                <h1 class="mb-0 fs-3">DAFTAR LINE</h1>
                <div class="text-secondary small">
                    IE LINE: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?> ·
                    Status: <span id="audit-session-status" class="fw-semibold"><?= strtoupper(esc($session['status'])) ?></span>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <?php if ($isAdmin): ?>
                    <a href="<?= base_url('audit-andon') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> DAFTAR SESI
                    </a>
                <?php endif; ?>

                <?php if (!$editable && $isAdmin): ?>
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
            <div class="row g-3 audit-line-grid">
                <?php foreach ($lines as $l): ?>
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <a href="<?= base_url($base . '/line/' . $l['line']) ?>" class="audit-line-link">
                            <div class="card audit-line-card h-100">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="audit-line-icon">
                                                <i class="fa-solid fa-table-cells-large"></i>
                                            </span>
                                            <div>
                                                <div class="audit-line-caption">IE LINE</div>
                                                <div class="audit-line-title">LINE <?= (int) $l['line'] ?></div>
                                            </div>
                                        </div>
                                        <span class="audit-line-arrow" aria-hidden="true">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </span>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="audit-line-stat">
                                                <div class="audit-line-stat-label">Jumlah Button</div>
                                                <div class="audit-line-stat-value"><?= (int) $l['total'] ?></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="audit-line-stat audit-line-stat-done">
                                                <div class="audit-line-stat-label">Sudah Diaudit</div>
                                                <div class="audit-line-stat-value"><?= (int) $l['audited'] ?></div>
                                            </div>
                                        </div>
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
