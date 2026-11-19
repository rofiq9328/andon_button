<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .andon-card {
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .andon-card:hover {
        transform: translateY(-3px);
    }

    .line-title {
        font-size: 1.1rem;
        font-weight: 700;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$total  = array_sum(array_column($lines, 'total'));
$terisi = array_sum(array_column($lines, 'terisi'));
$pctAll = $total > 0 ? (int) round($terisi / $total * 100) : 0;
?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1 fs-3">AUDIT ANDON <?= esc($session['periode']) ?></h1>
                <div class="text-secondary small">
                    PIC: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?> ·
                    Status: <span class="fw-bold"><?= esc($session['status']) ?></span>
                    <?php if (!$editable): ?>
                        · <span class="badge text-bg-dark">TERKUNCI</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <?php if ($isAdmin): ?>
                    <a href="<?= base_url('audit-andon') ?>" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> DAFTAR
                    </a>
                <?php endif; ?>

                <?php if ($editable): ?>
                    <form method="post" action="<?= base_url('audit-andon/simpan') ?>"
                          onsubmit="return confirm('Pastikan semua line sudah tersimpan. Serahkan data bulan ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
                        <button type="submit" name="action" value="serahkan" class="btn btn-success btn-sm fw-bold shadow-sm">
                            <i class="fa-solid fa-paper-plane me-1"></i> SERAHKAN
                        </button>
                    </form>
                <?php elseif ($isAdmin): ?>
                    <form method="post" action="<?= base_url('audit-andon/buka/' . (int) $session['id']) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm">
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

        <?php if ($editable && $session['status'] === 'draft' && (int) date('j') >= 25): ?>
            <div class="alert alert-warning shadow-sm">Sudah tanggal 25. Silakan serahkan data bulan ini.</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-bold text-dark mb-0">Daftar Line</h3>
                        <span class="small text-secondary">Terisi <?= $terisi ?> dari <?= $total ?> unit (<?= $pctAll ?>%)</span>
                    </div>

                    <div class="card-body">
                        <?php if (empty($lines)): ?>
                            <div class="alert alert-info mb-0">Belum ada area yang ditugaskan.</div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($lines as $l): ?>
                                    <?php
                                    $done  = $l['total'] > 0 && $l['terisi'] === $l['total'];
                                    $mulai = $l['terisi'] > 0 && !$done;
                                    $warna = $done ? 'success' : ($mulai ? 'warning' : 'secondary');
                                    $pct   = $l['total'] > 0 ? (int) round($l['terisi'] / $l['total'] * 100) : 0;
                                    ?>
                                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                                        <a href="<?= base_url($base . '/line/' . $l['line']) ?>" class="text-decoration-none">
                                            <div class="card andon-card card-outline card-<?= $warna ?> shadow-sm h-100">
                                                <div class="card-body text-center">
                                                    <div class="line-title text-dark">LINE <?= $l['line'] ?></div>
                                                    <div class="small text-secondary"><?= $l['terisi'] ?> / <?= $l['total'] ?> unit</div>
                                                    <div class="progress progress-sm mt-2">
                                                        <div class="progress-bar bg-<?= $warna ?>" style="width: <?= $pct ?>%"></div>
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
            </div>
        </div>

        <?php if (!$editable): ?>
            <div class="alert alert-secondary mt-3 shadow-sm">
                Sesi ini terkunci. Hanya admin yang bisa membukanya kembali.
            </div>
        <?php endif; ?>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>
