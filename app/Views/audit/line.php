<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<?= $this->include('audit/_styles') ?>
<style>
    /* ===== Tabel input: batas jelas dan header tetap terlihat ===== */
    .audit-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .audit-table th,
    .audit-table td {
        border-bottom: 1px solid var(--bs-border-color);
        border-right: 1px solid var(--bs-border-color-translucent);
        padding: 0.3rem 0.4rem;
    }

    .audit-table thead tr:nth-child(1) th {
        position: sticky;
        top: 0;
        z-index: 3;
        height: 34px;
        background-color: var(--bs-tertiary-bg);
        border-top: 0;
    }

    .audit-table thead tr:nth-child(2) th {
        position: sticky;
        top: 34px;
        z-index: 3;
        height: 30px;
        background-color: var(--bs-body-bg);
        box-shadow: inset 0 -2px 0 var(--bs-border-color);
    }

    .audit-table thead th.grp-head {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--bs-body-color);
    }

    .audit-table thead th.grp-head i {
        color: var(--bs-secondary-color);
        margin-right: 0.35rem;
    }

    .audit-table thead th.grp-head.grp-alt {
        background-color: var(--bs-secondary-bg);
    }

    .audit-table thead th.col-label {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
    }

    .audit-table th.grp-start,
    .audit-table td.grp-start {
        border-left: 2px solid var(--bs-secondary-color) !important;
    }

    .audit-table td.grp-alt {
        background-color: var(--bs-tertiary-bg);
    }

    .audit-table td.cell-ok {
        background-color: rgba(25, 135, 84, 0.13);
    }

    .audit-table td.cell-bad {
        background-color: rgba(220, 53, 69, 0.15);
    }

    .audit-table td.cell-empty {
        background-image: none;
        background-color: var(--bs-tertiary-bg);
    }

    .audit-table .sticky-col {
        position: sticky;
        left: 0;
        z-index: 2;
        border-right: 2px solid var(--bs-border-color);
        min-width: 92px;
    }

    .audit-table thead th.sticky-col {
        z-index: 5;
    }

    .audit-table tbody tr:hover .sticky-col {
        background-color: var(--bs-tertiary-bg) !important;
    }

    .audit-table tr[data-state="ok"] .sticky-col      { box-shadow: inset 4px 0 0 var(--bs-success); }
    .audit-table tr[data-state="bad"] .sticky-col     { box-shadow: inset 4px 0 0 var(--bs-danger); }
    .audit-table tr[data-state="partial"] .sticky-col { box-shadow: inset 4px 0 0 var(--bs-warning); }
    .audit-table tr[data-state="empty"] .sticky-col   { box-shadow: inset 4px 0 0 var(--bs-secondary); }

    /* ===== Tombol pilihan ===== */
    .audit-table .btn-group .btn {
        min-width: 54px;
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.15rem 0.35rem;
        white-space: nowrap;
    }

    .audit-table .btn-group .btn i {
        font-size: 0.55rem;
        margin-right: 0.15rem;
    }

    /* ===== Indikator 13 kolom per unit ===== */
    .seg-bar {
        display: flex;
        gap: 1px;
        margin-top: 0.25rem;
    }

    .seg-bar span {
        width: 4px;
        height: 8px;
        border-radius: 2px;
        background-color: var(--bs-secondary-bg);
        transition: background-color 0.2s;
    }

    .seg-bar span.seg-ok  { background-color: var(--bs-success); }
    .seg-bar span.seg-bad { background-color: var(--bs-danger); }

    .unit-no {
        font-size: 0.85rem;
    }

    .unit-count {
        font-size: 0.65rem;
        color: var(--bs-secondary-color);
        font-variant-numeric: tabular-nums;
        margin-top: 0;
    }

    /* Area tabel dan toolbar lebih ringkas */
    .audit-wrap {
        max-height: 64vh;
    }

    .audit-toolbar {
        padding: 0.6rem 0.8rem;
    }

    .audit-toolbar .status-pill {
        padding: 0.2rem 0.6rem;
    }

    /* ===== Versi lebih kecil ===== */
    .audit-wrap {
        max-height: 60vh;
    }

    .audit-toolbar {
        padding: 0.45rem 0.6rem;
    }

    .audit-toolbar .status-pill {
        font-size: 0.7rem;
    }

    .audit-toolbar .btn-sm,
    .audit-toolbar .form-select-sm {
        font-size: 0.7rem;
        padding-top: 0.12rem;
        padding-bottom: 0.12rem;
    }

    .audit-table th,
    .audit-table td {
        padding: 0.15rem 0.3rem;
    }

    .audit-table thead tr:nth-child(1) th {
        height: 26px;
    }

    .audit-table thead tr:nth-child(2) th {
        top: 26px;
        height: 48px;
    }

    .audit-table thead th.grp-head {
        font-size: 0.6rem;
    }

    .audit-table thead th.col-label {
        font-size: 0.6rem;
    }

    .audit-table .sticky-col {
        min-width: 74px;
    }

    .audit-table .btn-group .btn {
        min-width: 44px;
        font-size: 0.6rem;
        padding: 0.05rem 0.2rem;
        line-height: 1.4;
    }

    .audit-table .btn-group .btn i {
        font-size: 0.48rem;
        margin-right: 0.1rem;
    }

    .seg-bar {
        margin-top: 0.15rem;
    }

    .seg-bar span {
        width: 3px;
        height: 6px;
    }

    .unit-no {
        font-size: 0.75rem;
    }

    .unit-count {
        font-size: 0.56rem;
    }

    .audit-table .led-dot {
        width: 8px;
        height: 8px;
    }

    .audit-card > .card-footer {
        padding: 0.25rem 0.6rem;
        font-size: 0.65rem;
    }
    /* ===== Per-cell visual refinement (layout and input behavior unchanged) ===== */
    .audit-table td[data-field] {
        padding: 0.2rem 0.24rem;
        border: 1px solid rgba(108, 117, 125, 0.38);
        border-radius: 7px;
        background-clip: padding-box;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
        vertical-align: middle;
    }

    .audit-table td[data-field].cell-empty {
        background-color: rgba(108, 117, 125, 0.065);
        border-color: rgba(108, 117, 125, 0.38);
    }

    .audit-table td[data-field].cell-ok {
        background-color: rgba(25, 135, 84, 0.085);
        border-color: rgba(25, 135, 84, 0.42);
    }

    .audit-table td[data-field].cell-bad {
        background-color: rgba(220, 53, 69, 0.09);
        border-color: rgba(220, 53, 69, 0.44);
    }

    .audit-table td[data-field] .btn-group {
        display: inline-flex;
        gap: 2px;
        padding: 2px;
        border: 1px solid rgba(108, 117, 125, 0.24);
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.52);
    }

    .audit-table td[data-field] .btn-group .btn {
        min-width: 42px;
        border-radius: 4px;
    }

    .audit-table td[data-field].cell-ok .btn-group {
        border-color: rgba(25, 135, 84, 0.22);
    }

    .audit-table td[data-field].cell-bad .btn-group {
        border-color: rgba(220, 53, 69, 0.24);
    }

    
    /* Jarak tipis antar cell pemeriksaan, tanpa mengubah struktur tabel. */
    .audit-table td[data-field] {
        border: 2px solid var(--bs-body-bg) !important;
        border-radius: 6px;
        background-clip: padding-box;
        box-shadow: inset 0 0 0 1px rgba(108, 117, 125, 0.30);
    }

    .audit-table td[data-field].grp-start {
        border-left: 3px solid var(--bs-secondary-color) !important;
    }

    .audit-table .audit-column-header {
        min-width: 100px;
        padding: 0.28rem 0.2rem !important;
        vertical-align: middle;
    }

    .audit-column-title {
        margin-bottom: 0.18rem;
        line-height: 1.15;
        white-space: nowrap;
    }

    .audit-column-actions {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2px;
    }

    /* Header tabel: seluruh judul kapital dan tebal. Warna hanya pada sel nama grup. */
    .audit-table thead th {
        text-transform: uppercase !important;
        font-weight: 800 !important;
        letter-spacing: 0.055em;
    }

    .audit-table thead tr:first-child th.grp-head.grp-material {
        background: #dc3545 !important;
        color: #fff !important;
        border-bottom: 2px solid #b02a37;
    }

    .audit-table thead tr:first-child th.grp-head.grp-mekanik {
        background: #ffc107 !important;
        color: #332701 !important;
        border-bottom: 2px solid #d39e00;
    }

    .audit-table thead tr:first-child th.grp-head.grp-qc {
        background: #198754 !important;
        color: #fff !important;
        border-bottom: 2px solid #146c43;
    }

    .audit-table thead tr:first-child th.grp-head.grp-spv {
        background: #0d6efd !important;
        color: #fff !important;
        border-bottom: 2px solid #0a58ca;
    }

    .audit-table thead tr:first-child th.grp-head.grp-material i,
    .audit-table thead tr:first-child th.grp-head.grp-qc i,
    .audit-table thead tr:first-child th.grp-head.grp-spv i {
        color: inherit;
    }

    .audit-table thead tr:first-child th.grp-head.grp-mekanik i {
        color: inherit;
    }

    .audit-column-title {
        font-weight: 800;
    }

    .audit-column-actions .btn {
        flex: 1 1 0;
        min-width: 0;
        min-height: 28px;
        padding: 0.18rem 0.2rem;
        border-width: 1px;
        border-radius: 6px !important;
        font-size: 0.59rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: 0.015em;
        text-transform: uppercase;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(33, 37, 41, 0.10);
        transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
    }

    .audit-column-actions .btn:hover {
        box-shadow: 0 2px 5px rgba(33, 37, 41, 0.16);
        transform: translateY(-1px);
    }

    .audit-column-actions .btn:active {
        transform: translateY(0);
        box-shadow: none;
    }

    .audit-column-actions .btn-outline-success {
        background: rgba(25, 135, 84, 0.07);
    }

    .audit-column-actions .btn-outline-danger {
        background: rgba(220, 53, 69, 0.07);
    }


    /* ===== Mode ringkas PC: tabel mengikuti area layar ===== */
    @media (min-width: 1200px) {
        .app-content-header {
            padding-top: 0.35rem;
            padding-bottom: 0.2rem;
        }

        .app-content-header .container-fluid,
        .app-content > .container-fluid {
            padding-left: 0.55rem;
            padding-right: 0.55rem;
        }

        .app-content-header h1 {
            font-size: 1.2rem !important;
            line-height: 1.15;
        }

        .app-content-header .text-secondary.small {
            font-size: 0.68rem;
        }

        .app-content {
            padding-top: 0.2rem;
            padding-bottom: 0.35rem;
        }

        .audit-card {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 205px);
            min-height: 360px;
            border-radius: 8px;
        }

        .audit-card > .card-body {
            flex: 1 1 auto;
            min-height: 0;
        }

        .audit-wrap {
            height: 100%;
            max-height: none;
            overflow: auto;
        }

        .audit-table {
            width: 100%;
            table-layout: fixed;
            font-size: 0.62rem;
        }

        .audit-table th,
        .audit-table td {
            padding: 0.08rem 0.08rem;
        }

        .audit-table th:first-child,
        .audit-table td:first-child {
            width: 46px;
            min-width: 46px;
        }

        .audit-table thead tr:first-child th {
            height: 24px;
            font-size: 0.57rem;
        }

        .audit-table thead tr:nth-child(2) th {
            top: 24px;
            height: 40px;
            font-size: 0.53rem;
        }

        .audit-table thead th.grp-head {
            font-size: 0.58rem;
            letter-spacing: 0.025em;
        }

        .audit-table thead th.col-label {
            font-size: 0.52rem;
            letter-spacing: 0.015em;
        }

        .audit-table .audit-column-header {
            min-width: 0 !important;
            padding: 0.12rem 0.06rem !important;
        }

        .audit-column-title {
            margin-bottom: 0.1rem;
            font-size: 0.54rem;
            line-height: 1.05;
        }

        .audit-column-actions {
            gap: 1px;
        }

        .audit-column-actions .btn {
            min-width: 0;
            min-height: 21px;
            padding: 0.08rem 0.04rem;
            font-size: 0.47rem;
            line-height: 1.05;
            letter-spacing: 0;
            white-space: normal;
            overflow-wrap: anywhere;
            border-radius: 4px !important;
        }

        .audit-table td[data-field] {
            padding: 0.1rem 0.06rem;
            border-width: 1px !important;
            border-radius: 4px;
        }

        .audit-table td[data-field] .btn-group {
            display: flex;
            width: 100%;
            gap: 1px;
            padding: 1px;
            border-radius: 4px;
        }

        .audit-table td[data-field] .btn-group .btn {
            flex: 1 1 50%;
            min-width: 0;
            padding: 0.08rem 0.02rem;
            font-size: 0.49rem;
            line-height: 1.05;
            white-space: normal;
            overflow-wrap: anywhere;
            border-radius: 3px;
        }

        .audit-table td[data-field] .btn-group .btn i {
            display: none;
        }

        .audit-table .sticky-col {
            min-width: 46px;
        }

        .unit-no {
            font-size: 0.66rem;
        }

        .audit-card > .card-footer {
            flex: 0 0 auto;
            padding: 0.18rem 0.45rem;
            font-size: 0.56rem;
            line-height: 1.2;
        }
    }

    @media (min-width: 1600px) {
        .audit-table {
            font-size: 0.68rem;
        }

        .audit-table thead tr:nth-child(2) th {
            height: 42px;
        }

        .audit-column-title {
            font-size: 0.58rem;
        }

        .audit-column-actions .btn,
        .audit-table td[data-field] .btn-group .btn {
            font-size: 0.53rem;
        }

    }


    /* ===== Striping baris agar pembacaan unit lebih mudah ===== */
    .audit-table tbody tr:nth-child(even) td {
        background-image: linear-gradient(
            rgba(71, 85, 105, 0.055),
            rgba(71, 85, 105, 0.055)
        ) !important;
    }

    .audit-table tbody tr:hover td {
        background-image: linear-gradient(
            rgba(13, 110, 253, 0.075),
            rgba(13, 110, 253, 0.075)
        ) !important;
    }

</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Setiap pilihan: true = normal (hijau), false = perlu perhatian (merah)
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

$groupIcons = [
    'Remote'    => 'fa-satellite-dish',
    'Material'  => 'fa-boxes-stacked',
    'Mekanik'   => 'fa-gears',
    'QC/QA'     => 'fa-magnifying-glass-chart',
    'SPV'       => 'fa-user-tie',
    'Perbaikan' => 'fa-screwdriver-wrench',
];

$groupHeaderClasses = [
    'Material'  => 'grp-material',
    'Mekanik'   => 'grp-mekanik',
    'QC/QA'     => 'grp-qc',
    'SPV'       => 'grp-spv',
];

$flat = [];
$gi = 0;
foreach ($groups as $gName => $fields) {
    $first = true;
    foreach ($fields as $key => $def) {
        $flat[$key] = $def + ['grp' => $gi, 'start' => $first, 'group' => $gName];
        $first = false;
    }
    $gi++;
}
$fieldCount = count($flat);
$lineTotal  = count($units);
?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <div class="text-secondary small text-uppercase fw-semibold">Audit Andon <?= esc($session['periode']) ?></div>
                <h1 class="mb-0 fs-3">LINE <?= (int) $line ?></h1>
                <div class="text-secondary small">
                    PIC: <?= esc($owner['full_name'] ?? $owner['username'] ?? '-') ?>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <a href="<?= base_url($base) ?>" class="btn btn-outline-secondary btn-sm fw-bold">
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
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Berhasil disimpan',
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        });
                    }
                });
            </script>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php if ($editable): ?>
            <form id="column-fill-form" method="post" action="<?= base_url('audit-andon/isi-cepat') ?>" class="d-none">
                <?= csrf_field() ?>
                <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
                <input type="hidden" name="line" value="<?= (int) $line ?>">
                <input type="hidden" name="field" id="column-fill-field">
                <input type="hidden" name="value" id="column-fill-value">
            </form>
        <?php endif; ?>

        <!-- Tabel unit -->
        <div class="card audit-card">
            <div class="card-body p-0">
                <div class="table-responsive audit-wrap">
                    <table class="table table-sm align-middle mb-0 audit-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col col-label text-start">Unit</th>
                                <?php $gIdx = 0; foreach ($groups as $gName => $fields): ?>
                                    <th colspan="<?= count($fields) ?>"
                                        class="text-center grp-head<?= $gIdx % 2 ? ' grp-alt' : '' ?><?= $gIdx > 0 ? ' grp-start' : '' ?> <?= esc($groupHeaderClasses[$gName] ?? '') ?>">
                                        <i class="fa-solid <?= $groupIcons[$gName] ?? 'fa-circle' ?>"></i><?= esc($gName) ?>
                                    </th>
                                    <?php $gIdx++; ?>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <?php foreach ($flat as $key => $def): ?>
                                    <th class="text-center col-label audit-column-header<?= $def['start'] ? ' grp-start' : '' ?><?= $def['grp'] % 2 ? ' grp-alt' : '' ?>">
                                        <div class="audit-column-title"><?= esc($def['label']) ?></div>
                                        <?php if ($editable): ?>
                                            <div class="audit-column-actions">
                                                <?php foreach ($def['opts'] as [$optionValue, $optionGood]): ?>
                                                    <button type="button"
                                                            class="btn btn-outline-<?= $optionGood ? 'success' : 'danger' ?> audit-column-fill"
                                                            data-field="<?= esc($key) ?>"
                                                            data-value="<?= esc($optionValue) ?>"
                                                            data-label="<?= esc($def['group'] . ' · ' . $def['label']) ?>"
                                                            title="Terapkan <?= esc($optionValue) ?> ke semua unit">
                                                        <?= esc($optionValue) ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($units as $u): ?>
                                <tr id="unit-<?= (int) $u['id'] ?>" data-unit="<?= (int) $u['id'] ?>" data-state="empty">
                                    <td class="sticky-col bg-body text-center">
                                        <span class="unit-no fw-bold"><?= (int) $u['line_no'] ?></span>
                                    </td>

                                    <?php foreach ($flat as $key => $def): ?>
                                        <td class="text-center<?= $def['start'] ? ' grp-start' : '' ?><?= $def['grp'] % 2 ? ' grp-alt' : '' ?>"
                                            data-field="<?= $key ?>">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php foreach ($def['opts'] as $i => [$value, $good]): ?>
                                                    <?php $rid = 'u' . (int) $u['id'] . '_' . $key . '_' . $i; ?>
                                                    <input type="radio" class="btn-check audit-radio"
                                                           name="u<?= (int) $u['id'] ?>_<?= $key ?>"
                                                           id="<?= $rid ?>"
                                                           value="<?= esc($value) ?>"
                                                           data-unit="<?= (int) $u['id'] ?>"
                                                           data-field="<?= $key ?>"
                                                           data-bad="<?= $good ? '0' : '1' ?>"
                                                           autocomplete="off"
                                                           <?= ($u[$key] === $value) ? 'checked' : '' ?>
                                                           <?= $editable ? '' : 'disabled' ?>>
                                                    <label class="btn btn-outline-<?= $good ? 'success' : 'danger' ?>"
                                                           for="<?= $rid ?>">
                                                        <i class="fa-solid <?= $good ? 'fa-check' : 'fa-xmark' ?>"></i><?= esc($value) ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($editable): ?>
                <div class="card-footer small text-secondary d-flex justify-content-between flex-wrap gap-2">
                    <span><i class="fa-solid fa-cloud-arrow-up me-1"></i> Perubahan tersimpan otomatis.</span>
                    <span>Tiap garis kecil di bawah nomor unit mewakili satu kolom: hijau normal, merah perlu perhatian, abu-abu belum diisi.</span>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const CFG = {
        url:        <?= json_encode(base_url('audit-andon/autosave')) ?>,
        sessionId:  <?= (int) $session['id'] ?>,
        fieldCount: <?= (int) $fieldCount ?>,
        editable:   <?= $editable ? 'true' : 'false' ?>,
        csrfName:   <?= json_encode(csrf_token()) ?>,
        csrfHash:   <?= json_encode(csrf_hash()) ?>,
    };

    const tableBody = document.querySelector('.audit-table tbody');
    const rows = tableBody ? Array.from(tableBody.querySelectorAll('tr[data-unit]')) : [];
    const statusEl = document.getElementById('save-status');
    const PILL = { idle: 'is-idle', saving: 'is-saving', saved: 'is-saved', error: 'is-error' };
    const ICON = {
        idle:   'fa-solid fa-circle-check',
        saving: 'fa-solid fa-circle-notch',
        saved:  'fa-solid fa-circle-check',
        error:  'fa-solid fa-triangle-exclamation',
    };

    let inFlight = 0;

    function setStatus(kind, text) {
        if (!statusEl) return;
        statusEl.className = 'status-pill ' + PILL[kind];
        statusEl.querySelector('i').className = ICON[kind];
        statusEl.querySelector('span').textContent = text;
    }

    function setCellState(td, selected) {
        const filled = !!selected;
        const bad = filled && selected.dataset.bad === '1';

        td.classList.toggle('cell-ok', filled && !bad);
        td.classList.toggle('cell-bad', bad);
        td.classList.toggle('cell-empty', !filled);
        td.dataset.auditFilled = filled ? '1' : '0';
        td.dataset.auditBad = bad ? '1' : '0';

        return { filled: filled, bad: bad };
    }

    function setRowState(tr, filled, badCount) {
        let state = 'empty';
        if (badCount > 0) {
            state = 'bad';
        } else if (filled === CFG.fieldCount) {
            state = 'ok';
        } else if (filled > 0) {
            state = 'partial';
        }

        tr.dataset.auditFilled = String(filled);
        tr.dataset.auditBad = String(badCount);
        tr.dataset.state = state;
    }

    // Hitung sekali saat halaman dibuka; selanjutnya hanya sel yang berubah yang diperbarui.
    rows.forEach(function (tr) {
        let filled = 0;
        let badCount = 0;

        tr.querySelectorAll('td[data-field]').forEach(function (td) {
            const selected = td.querySelector('input.audit-radio:checked');
            const state = setCellState(td, selected);
            if (state.filled) filled++;
            if (state.bad) badCount++;
        });

        setRowState(tr, filled, badCount);
    });

    async function autosave(payload) {
        inFlight++;
        setStatus('saving', 'Menyimpan…');

        const body = new URLSearchParams({ session_id: CFG.sessionId, ...payload });
        body.append(CFG.csrfName, CFG.csrfHash);

        try {
            const res = await fetch(CFG.url, {
                method: 'POST',
                body: body,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.csrf) CFG.csrfHash = data.csrf;
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'Gagal menyimpan');
            }

            inFlight--;
            if (inFlight === 0) {
                // Status toolbar kembali netral; notifikasi sukses hanya melalui SweetAlert2.
                setStatus('idle', 'Siap');

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Berhasil disimpan',
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        });
                }
            }
        } catch (e) {
            inFlight--;
            setStatus('error', 'Gagal menyimpan: ' + e.message);
        }
    }

    // Satu listener untuk seluruh pilihan audit dan kolom catatan.
    if (tableBody) {
        tableBody.addEventListener('change', function (event) {
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) return;

            if (target.matches('input.audit-radio')) {
                const tr = target.closest('tr[data-unit]');
                const td = target.closest('td[data-field]');
                if (!tr || !td) return;

                const wasFilled = td.dataset.auditFilled === '1';
                const wasBad = td.dataset.auditBad === '1';
                const isBad = target.dataset.bad === '1';

                let filled = Number(tr.dataset.auditFilled || 0);
                let badCount = Number(tr.dataset.auditBad || 0);

                if (!wasFilled) filled++;
                if (wasBad) badCount--;
                if (isBad) badCount++;

                setCellState(td, target);
                setRowState(tr, filled, badCount);

                if (CFG.editable) {
                    autosave({
                        unit_id: target.dataset.unit,
                        field: target.dataset.field,
                        value: target.value
                    });
                }
                return;
            }

        });
    }

    // Pilihan di header menerapkan satu nilai ke seluruh unit pada kolom terkait.
    const columnFillForm = document.getElementById('column-fill-form');
    const auditTable = document.querySelector('.audit-table');

    if (auditTable && columnFillForm) {
        auditTable.addEventListener('click', function (event) {
            const button = event.target.closest('button.audit-column-fill');
            if (!button || !CFG.editable) return;

            if (inFlight > 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Penyimpanan berlangsung',
                        text: 'Tunggu sampai penyimpanan selesai, lalu coba lagi.',
                        icon: 'info',
                        confirmButtonColor: '#0d6efd',
                        confirmButtonText: 'OK'
                    });
                }
                return;
            }

            const field = button.dataset.field;
            const value = button.dataset.value;
            const label = button.dataset.label || field;
            const unitCount = rows.length;

            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 belum tersedia pada halaman ini.');
                return;
            }

            Swal.fire({
                title: 'Terapkan ke Semua Unit?',
                text: 'Nilai "' + value + '" pada kolom ' + label + ' akan diterapkan ke ' + unitCount +
                      ' unit. Isian lama pada kolom ini akan diganti.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Terapkan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                columnFillForm.elements['field'].value = field;
                columnFillForm.elements['value'].value = value;
                columnFillForm.submit();
            });
        });
    }

    window.addEventListener('beforeunload', function (e) {
        if (inFlight > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
})();
</script>
<?= $this->endSection() ?>
