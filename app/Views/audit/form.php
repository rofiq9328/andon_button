<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
$total  = array_sum(array_column($lines, 'total'));
$terisi = array_sum(array_column($lines, 'terisi'));
?>

<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Audit Andon <?= esc($session['periode']) ?></h1>
            <div class="text-muted small">
                PIC: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?> ·
                Status: <?= esc($session['status']) ?> ·
                Terisi <?= $terisi ?> dari <?= $total ?> unit
            </div>
        </div>
        <?php if ($isAdmin): ?>
            <a href="<?= base_url('audit-andon') ?>" class="btn btn-sm btn-outline-secondary">Kembali ke daftar</a>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if ($editable && $session['status'] === 'draft' && (int) date('j') >= 25): ?>
        <div class="alert alert-warning">
            Sudah tanggal 25. Silakan serahkan data bulan ini.
        </div>
    <?php endif; ?>

    <?php if (empty($lines)): ?>
        <div class="alert alert-info">Belum ada area yang ditugaskan.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($lines as $l): ?>
                <?php
                $done = $l['total'] > 0 && $l['terisi'] === $l['total'];
                $pct  = $l['total'] > 0 ? (int) round($l['terisi'] / $l['total'] * 100) : 0;
                ?>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <a href="<?= base_url($base . '/line/' . $l['line']) ?>" class="text-decoration-none">
                        <div class="card h-100 <?= $done ? 'border-success' : '' ?>">
                            <div class="card-body text-center">
                                <div class="fs-5 fw-bold text-body">Line <?= $l['line'] ?></div>
                                <div class="small text-muted"><?= $l['terisi'] ?> / <?= $l['total'] ?> unit</div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar <?= $done ? 'bg-success' : '' ?>"
                                         style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($editable): ?>
        <div class="d-flex justify-content-end mt-4">
            <form method="post" action="<?= base_url('audit-andon/simpan') ?>"
                  onsubmit="return confirm('Pastikan semua line sudah tersimpan. Serahkan data bulan ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
                <button type="submit" name="action" value="serahkan" class="btn btn-success">Serahkan</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary mt-4">
            Sesi ini terkunci. Hanya admin yang bisa membukanya kembali.
        </div>
        <?php if ($isAdmin): ?>
            <form method="post" action="<?= base_url('audit-andon/buka/' . (int) $session['id']) ?>" class="mt-3">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning">Buka Kembali Sesi</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>
