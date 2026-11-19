<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
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

$lineTotal  = count($units);
$lineTerisi = count(array_filter($units, static fn ($u) => $u['kondisi_remote'] !== null));
?>

<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Line <?= (int) $line ?></h1>
            <div class="text-muted small">
                Audit Andon <?= esc($session['periode']) ?> ·
                PIC: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?> ·
                Terisi <?= $lineTerisi ?> dari <?= $lineTotal ?> unit
            </div>
        </div>
        <a href="<?= base_url($base) ?>" class="btn btn-sm btn-outline-secondary">Kembali ke daftar line</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <?php if ($prev !== null): ?>
            <a href="<?= base_url($base . '/line/' . $prev) ?>" class="btn btn-sm btn-outline-primary">← Line <?= $prev ?></a>
        <?php else: ?>
            <span></span>
        <?php endif; ?>

        <?php if ($next !== null): ?>
            <a href="<?= base_url($base . '/line/' . $next) ?>" class="btn btn-sm btn-outline-primary">Line <?= $next ?> →</a>
        <?php endif; ?>
    </div>

    <form method="post" action="<?= base_url('audit-andon/simpan') ?>" class="card">
        <?= csrf_field() ?>
        <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
        <input type="hidden" name="line" value="<?= (int) $line ?>">

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

        <?php if ($editable): ?>
            <div class="card-footer text-end">
                <button type="submit" name="action" value="draft" class="btn btn-primary">
                    Simpan Line <?= (int) $line ?>
                </button>
            </div>
        <?php endif; ?>
    </form>

    <?php if (!$editable): ?>
        <div class="alert alert-secondary mt-3">
            Sesi ini terkunci. Hanya admin yang bisa membukanya kembali.
        </div>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>
