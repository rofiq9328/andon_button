<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
// Definisi kolom sesuai dropdown di file Excel
$fieldDefs = [
    'tersedia'        => ['label' => 'Tersedia',        'opts' => ['ADA', 'TIDAK ADA']],
    'kondisi_remote'  => ['label' => 'Kondisi Remote',  'opts' => ['ON', 'OFF']],
    'visual_nomor'    => ['label' => 'Visual Nomor',    'opts' => ['ADA', 'TIDAK ADA']],
    'material_lampu'  => ['label' => 'Material Lampu',  'opts' => ['ON', 'OFF']],
    'material_layar'  => ['label' => 'Material Layar',  'opts' => ['MUNCUL', 'TIDAK']],
    'mekanik_lampu'   => ['label' => 'Mekanik Lampu',   'opts' => ['ON', 'OFF']],
    'mekanik_layar'   => ['label' => 'Mekanik Layar',   'opts' => ['MUNCUL', 'TIDAK']],
    'qc_lampu'        => ['label' => 'QC Lampu',        'opts' => ['ON', 'OFF']],
    'qc_layar'        => ['label' => 'QC Layar',        'opts' => ['MUNCUL', 'TIDAK']],
    'spv_lampu'       => ['label' => 'SPV Lampu',       'opts' => ['ON', 'OFF']],
    'spv_layar'       => ['label' => 'SPV Layar',       'opts' => ['MUNCUL', 'TIDAK']],
    'perbaikan_rusak' => ['label' => 'Rusak',           'opts' => ['YES', 'NO']],
    'ganti_baterai'   => ['label' => 'Ganti Baterai',   'opts' => ['YES', 'NO']],
];

// Progres keseluruhan: unit yang kondisi_remote-nya sudah diisi
$total  = 0;
$terisi = 0;
foreach ($grouped as $units) {
    foreach ($units as $u) {
        $total++;
        if ($u['kondisi_remote'] !== null) {
            $terisi++;
        }
    }
}
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

    <?php if (empty($grouped)): ?>
        <div class="alert alert-info">Belum ada area yang ditugaskan.</div>
    <?php else: ?>
        <?php foreach ($grouped as $line => $units): ?>
            <?php
            $lineTotal  = count($units);
            $lineTerisi = count(array_filter($units, static fn ($u) => $u['kondisi_remote'] !== null));
            ?>
            <div class="card mb-3" id="line-<?= (int) $line ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Line <?= (int) $line ?></span>
                    <span class="small text-muted">Terisi <?= $lineTerisi ?> dari <?= $lineTotal ?></span>
                </div>

                <form method="post" action="<?= base_url('audit-andon/simpan') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
                    <input type="hidden" name="line" value="<?= (int) $line ?>">

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unit</th>
                                        <?php foreach ($fieldDefs as $def): ?>
                                            <th class="small"><?= esc($def['label']) ?></th>
                                        <?php endforeach; ?>
                                        <th class="small">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($units as $u): ?>
                                        <tr>
                                            <td class="text-nowrap">#<?= (int) $u['line_no'] ?></td>

                                            <?php foreach ($fieldDefs as $field => $def): ?>
                                                <td>
                                                    <select class="form-select form-select-sm"
                                                            name="units[<?= (int) $u['id'] ?>][<?= $field ?>]"
                                                            <?= $editable ? '' : 'disabled' ?>>
                                                        <option value="">-</option>
                                                        <?php foreach ($def['opts'] as $opt): ?>
                                                            <option value="<?= esc($opt) ?>"
                                                                <?= ($u[$field] === $opt) ? 'selected' : '' ?>>
                                                                <?= esc($opt) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            <?php endforeach; ?>

                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="units[<?= (int) $u['id'] ?>][catatan]"
                                                       value="<?= esc($u['catatan'] ?? '') ?>"
                                                       <?= $editable ? '' : 'disabled' ?>>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($editable): ?>
                        <div class="card-footer text-end">
                            <button type="submit" name="action" value="draft" class="btn btn-sm btn-primary">
                                Simpan Line <?= (int) $line ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($editable): ?>
        <form method="post" action="<?= base_url('audit-andon/simpan') ?>"
              class="sticky-bottom bg-white py-2 d-flex justify-content-end gap-2"
              onsubmit="return confirm('Pastikan semua line sudah disimpan. Serahkan data bulan ini?')">
            <?= csrf_field() ?>
            <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
            <button type="submit" name="action" value="serahkan" class="btn btn-success">Serahkan</button>
        </form>
    <?php else: ?>
        <div class="alert alert-secondary">
            Sesi ini terkunci. Hanya admin yang bisa membukanya kembali.
        </div>
    <?php endif; ?>

    <?php if ($isAdmin && !$editable): ?>
        <form method="post" action="<?= base_url('audit-andon/buka/' . (int) $session['id']) ?>" class="mt-3">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-warning">Buka Kembali Sesi</button>
        </form>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>
