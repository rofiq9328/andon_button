<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .audit-table td,
    .audit-table th {
        vertical-align: middle;
    }
</style>
<?= $this->endSection() ?>

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

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-1 fs-3">LINE <?= (int) $line ?></h1>
                <div class="text-secondary small">
                    Audit Andon <?= esc($session['periode']) ?> ·
                    PIC: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?> ·
                    Terisi <?= $lineTerisi ?> dari <?= $lineTotal ?> unit
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
                <a href="<?= base_url($base) ?>" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                    <i class="fa-solid fa-table-cells me-1"></i> SEMUA LINE
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <?php if ($prev !== null): ?>
                <a href="<?= base_url($base . '/line/' . $prev) ?>"
                   class="btn btn-outline-primary btn-sm fw-bold shadow-sm">
                    <i class="fa-solid fa-chevron-left me-1"></i> LINE <?= $prev ?>
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <?php if ($next !== null): ?>
                <a href="<?= base_url($base . '/line/' . $next) ?>"
                   class="btn btn-outline-primary btn-sm fw-bold shadow-sm">
                    LINE <?= $next ?> <i class="fa-solid fa-chevron-right ms-1"></i>
                </a>
            <?php endif; ?>
        </div>

        <form method="post" action="<?= base_url('audit-andon/simpan') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
            <input type="hidden" name="line" value="<?= (int) $line ?>">

            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title fw-bold text-dark mb-0">Unit Remote</h3>
                    <div class="card-tools small text-secondary">
                        <span class="text-danger fw-bold">Merah</span> = perlu perhatian
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered align-middle mb-0 audit-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">Unit</th>
                                    <?php foreach ($fieldDefs as $def): ?>
                                        <th class="small text-nowrap"><?= esc($def['label']) ?></th>
                                    <?php endforeach; ?>
                                    <th class="small">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($units as $u): ?>
                                    <tr>
                                        <td class="text-nowrap fw-bold">#<?= (int) $u['line_no'] ?></td>

                                        <?php foreach ($fieldDefs as $field => $def): ?>
                                            <td>
                                                <select class="form-select form-select-sm audit-field"
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
                        <button type="submit" name="action" value="draft" class="btn btn-success btn-sm fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN LINE <?= (int) $line ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!$editable): ?>
            <div class="alert alert-secondary mt-3 shadow-sm">
                Sesi ini terkunci. Hanya admin yang bisa membukanya kembali.
            </div>
        <?php endif; ?>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Nilai yang menandakan masalah diberi warna merah supaya mudah terlihat
    (function () {
        const bermasalah = ['OFF', 'TIDAK', 'TIDAK ADA', 'YES'];

        document.querySelectorAll('.audit-field').forEach(function (el) {
            function tandai() {
                const merah = bermasalah.includes(el.value);
                el.classList.toggle('text-danger', merah);
                el.classList.toggle('fw-bold', merah);
            }
            el.addEventListener('change', tandai);
            tandai();
        });
    })();
</script>
<?= $this->endSection() ?>
