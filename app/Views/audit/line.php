<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .audit-wrap {
        max-height: 70vh;
    }

    .audit-table th,
    .audit-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .audit-table .sticky-col {
        position: sticky;
        left: 0;
        z-index: 1;
    }

    .audit-table .btn-group .btn {
        min-width: 52px;
    }

    /* Indikator status per unit, sama seperti LED di dashboard */
    .led-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: currentColor;
        transition: box-shadow 0.3s;
    }

    .led-dot.is-active {
        box-shadow: 0 0 10px currentColor;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Setiap pilihan punya flag: true = kondisi normal (hijau), false = perlu perhatian (merah)
$groups = [
    'Remote' => [
        'tersedia'       => ['label' => 'Tersedia', 'opts' => [['ADA', true], ['TIDAK ADA', false]]],
        'kondisi_remote' => ['label' => 'Kondisi',  'opts' => [['ON', true], ['OFF', false]]],
        'visual_nomor'   => ['label' => 'Visual',   'opts' => [['ADA', true], ['TIDAK ADA', false]]],
    ],
    'Material' => [
        'material_lampu' => ['label' => 'Lampu', 'opts' => [['ON', true], ['OFF', false]]],
        'material_layar' => ['label' => 'Layar', 'opts' => [['MUNCUL', true], ['TIDAK', false]]],
    ],
    'Mekanik' => [
        'mekanik_lampu' => ['label' => 'Lampu', 'opts' => [['ON', true], ['OFF', false]]],
        'mekanik_layar' => ['label' => 'Layar', 'opts' => [['MUNCUL', true], ['TIDAK', false]]],
    ],
    'QC/QA' => [
        'qc_lampu' => ['label' => 'Lampu', 'opts' => [['ON', true], ['OFF', false]]],
        'qc_layar' => ['label' => 'Layar', 'opts' => [['MUNCUL', true], ['TIDAK', false]]],
    ],
    'SPV' => [
        'spv_lampu' => ['label' => 'Lampu', 'opts' => [['ON', true], ['OFF', false]]],
        'spv_layar' => ['label' => 'Layar', 'opts' => [['MUNCUL', true], ['TIDAK', false]]],
    ],
    'Perbaikan' => [
        'perbaikan_rusak' => ['label' => 'Rusak',   'opts' => [['NO', true], ['YES', false]]],
        'ganti_baterai'   => ['label' => 'Baterai', 'opts' => [['NO', true], ['YES', false]]],
    ],
];

// Susun semua field dalam urutan kolom
$flat = [];
foreach ($groups as $fields) {
    foreach ($fields as $key => $def) {
        $flat[$key] = $def;
    }
}
$fieldCount = count($flat);

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
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="card-title fw-bold text-dark mb-0">Unit Remote</h3>
                    <div class="d-flex flex-wrap gap-2 small">
                        <span class="badge text-bg-success">Lengkap <span id="sum-ok">0</span></span>
                        <span class="badge text-bg-danger">Perlu perhatian <span id="sum-bad">0</span></span>
                        <span class="badge text-bg-secondary">Belum lengkap <span id="sum-empty">0</span></span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive audit-wrap">
                        <table class="table table-sm table-bordered align-middle mb-0 audit-table">
                            <thead>
                                <tr class="text-center">
                                    <th rowspan="2" class="sticky-col bg-body-tertiary">Unit</th>
                                    <?php foreach ($groups as $gName => $fields): ?>
                                        <th colspan="<?= count($fields) ?>"
                                            class="bg-body-tertiary small text-uppercase"><?= esc($gName) ?></th>
                                    <?php endforeach; ?>
                                    <th rowspan="2" class="bg-body-tertiary small">Catatan</th>
                                </tr>
                                <tr class="text-center">
                                    <?php foreach ($flat as $def): ?>
                                        <th class="small fw-normal bg-body-tertiary"><?= esc($def['label']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($units as $u): ?>
                                    <tr data-unit="<?= (int) $u['id'] ?>">
                                        <td class="sticky-col bg-body fw-bold">
                                            <span class="led-dot text-secondary me-2" id="led-<?= (int) $u['id'] ?>"></span>#<?= (int) $u['line_no'] ?>
                                        </td>

                                        <?php foreach ($flat as $key => $def): ?>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php foreach ($def['opts'] as $i => [$value, $good]): ?>
                                                        <?php $rid = 'u' . (int) $u['id'] . '_' . $key . '_' . $i; ?>
                                                        <input type="radio" class="btn-check audit-radio"
                                                               name="units[<?= (int) $u['id'] ?>][<?= $key ?>]"
                                                               id="<?= $rid ?>"
                                                               value="<?= esc($value) ?>"
                                                               data-bad="<?= $good ? '0' : '1' ?>"
                                                               autocomplete="off"
                                                               <?= ($u[$key] === $value) ? 'checked' : '' ?>
                                                               <?= $editable ? '' : 'disabled' ?>>
                                                        <label class="btn btn-outline-<?= $good ? 'success' : 'danger' ?>"
                                                               for="<?= $rid ?>"><?= esc($value) ?></label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>

                                        <td>
                                            <input type="text" class="form-control form-control-sm" style="min-width: 160px;"
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
    (function () {
        const FIELD_COUNT = <?= (int) $fieldCount ?>;
        const rows = document.querySelectorAll('tr[data-unit]');

        // Warna LED per unit: merah jika ada pilihan bermasalah,
        // hijau jika semua field sudah diisi dan normal, abu-abu jika belum lengkap
        function refreshRow(tr) {
            const dot = document.getElementById('led-' + tr.dataset.unit);
            const checked = tr.querySelectorAll('input.audit-radio:checked');
            const adaMasalah = Array.from(checked).some(r => r.dataset.bad === '1');

            dot.classList.remove('text-success', 'text-danger', 'text-secondary', 'is-active');
            if (adaMasalah) {
                dot.classList.add('text-danger', 'is-active');
            } else if (checked.length === FIELD_COUNT) {
                dot.classList.add('text-success', 'is-active');
            } else {
                dot.classList.add('text-secondary');
            }
        }

        function refreshSummary() {
            let ok = 0;
            let bad = 0;
            rows.forEach(tr => {
                const dot = document.getElementById('led-' + tr.dataset.unit);
                if (dot.classList.contains('text-danger')) {
                    bad++;
                } else if (dot.classList.contains('text-success')) {
                    ok++;
                }
            });
            document.getElementById('sum-ok').textContent = ok;
            document.getElementById('sum-bad').textContent = bad;
            document.getElementById('sum-empty').textContent = rows.length - ok - bad;
        }

        rows.forEach(tr => {
            tr.querySelectorAll('input.audit-radio').forEach(r => {
                r.addEventListener('change', () => {
                    refreshRow(tr);
                    refreshSummary();
                });
            });
            refreshRow(tr);
        });
        refreshSummary();
    })();
</script>
<?= $this->endSection() ?>
