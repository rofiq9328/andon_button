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

    .note-input {
        min-width: 150px;
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
        height: 24px;
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

    .note-input {
        min-width: 110px;
    }

    .note-input .form-control,
    .note-input .input-group-text {
        font-size: 0.66rem;
        padding: 0.05rem 0.3rem;
    }

    .audit-card > .card-footer {
        padding: 0.25rem 0.6rem;
        font-size: 0.65rem;
    }


    /* ===== Grid pemeriksaan audit per unit ===== */
    .audit-unit-list {
        display: grid;
        gap: 0.85rem;
        padding: 0.85rem;
    }

    .audit-unit-card {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-left: 4px solid var(--bs-secondary);
        border-radius: 12px;
        background: var(--bs-body-bg);
    }

    .audit-unit-card[data-state="ok"] { border-left-color: var(--bs-success); }
    .audit-unit-card[data-state="bad"] { border-left-color: var(--bs-danger); }
    .audit-unit-card[data-state="partial"] { border-left-color: var(--bs-warning); }
    .audit-unit-card[data-state="empty"] { border-left-color: var(--bs-secondary); }

    .audit-unit-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        padding: 0.55rem 0.85rem;
        border-bottom: 1px solid var(--bs-border-color);
        background: var(--bs-tertiary-bg);
    }

    .audit-unit-identity,
    .audit-unit-summary {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .audit-unit-caption {
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: var(--bs-secondary-color);
    }

    .audit-unit-number {
        min-width: 2.1rem;
        font-size: 1.05rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        color: var(--bs-body-color);
    }

    .audit-unit-state {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: var(--bs-secondary-bg);
        color: var(--bs-secondary-color);
    }

    .audit-unit-card[data-state="ok"] .audit-unit-state {
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
    }

    .audit-unit-card[data-state="bad"] .audit-unit-state {
        background: var(--bs-danger-bg-subtle);
        color: var(--bs-danger-text-emphasis);
    }

    .audit-unit-card[data-state="partial"] .audit-unit-state {
        background: var(--bs-warning-bg-subtle);
        color: var(--bs-warning-text-emphasis);
    }

    .audit-unit-count {
        font-size: 0.68rem;
        color: var(--bs-secondary-color);
        font-variant-numeric: tabular-nums;
    }

    .audit-groups-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.6rem;
        padding: 0.7rem;
        align-items: stretch;
    }

    .audit-group-panel {
        min-width: 0;
        border: 1px solid rgba(108, 117, 125, 0.55);
        border-radius: 9px;
        overflow: hidden;
        background: var(--bs-body-bg);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .audit-group-head {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-height: 34px;
        padding: 0.42rem 0.6rem;
        font-size: 0.69rem;
        font-weight: 800;
        letter-spacing: 0.045em;
        text-transform: uppercase;
        background: var(--bs-secondary-bg);
        color: var(--bs-body-color);
        border-bottom: 2px solid rgba(108, 117, 125, 0.35);
    }

    .audit-group-head i { color: var(--bs-secondary-color); }

    .audit-group-fields {
        display: grid;
        gap: 0.5rem;
        padding: 0.55rem;
        background: var(--bs-tertiary-bg);
    }

    /* Setiap pemeriksaan dibuat seperti satu cell tersendiri agar batasnya jelas. */
    .audit-field {
        min-width: 0;
        min-height: 66px;
        padding: 0.45rem;
        border: 1px solid rgba(108, 117, 125, 0.55);
        border-left: 3px solid var(--bs-secondary);
        border-radius: 7px;
        background: var(--bs-body-bg);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .audit-field.cell-ok {
        background: rgba(25, 135, 84, 0.10);
        border-color: rgba(25, 135, 84, 0.50);
        border-left-color: var(--bs-success);
    }

    .audit-field.cell-bad {
        background: rgba(220, 53, 69, 0.10);
        border-color: rgba(220, 53, 69, 0.50);
        border-left-color: var(--bs-danger);
    }

    .audit-field.cell-empty {
        background: var(--bs-tertiary-bg);
        border-left-color: var(--bs-secondary);
    }

    .audit-field-title {
        margin: 0 0 0.42rem;
        padding-bottom: 0.32rem;
        border-bottom: 1px solid rgba(108, 117, 125, 0.30);
        font-size: 0.67rem;
        font-weight: 750;
        line-height: 1.2;
        color: var(--bs-body-color);
        text-transform: uppercase;
        letter-spacing: 0.035em;
    }

    .audit-choice-group {
        display: flex;
        width: 100%;
        gap: 0.25rem;
    }

    .audit-choice-group .btn {
        flex: 1 1 0;
        min-width: 0;
        min-height: 30px;
        padding: 0.27rem 0.25rem;
        border-width: 1.5px;
        font-size: 0.66rem;
        font-weight: 700;
        line-height: 1.25;
        white-space: normal;
    }

    .audit-choice-group .btn i {
        font-size: 0.58rem;
        margin-right: 0.12rem;
    }

    .audit-unit-note {
        display: grid;
        grid-template-columns: 115px minmax(0, 1fr);
        align-items: center;
        gap: 0.65rem;
        padding: 0.55rem 0.85rem 0.7rem;
        border-top: 1px solid var(--bs-border-color);
    }

    .audit-unit-note label {
        margin: 0;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--bs-secondary-color);
    }

    .audit-unit-note .form-control {
        font-size: 0.75rem;
    }

    .audit-card > .card-footer {
        padding: 0.55rem 0.85rem;
        border-top: 1px solid var(--bs-border-color);
    }

    @media (max-width: 1399.98px) {
        .audit-groups-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 767.98px) {
        .audit-groups-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .audit-unit-head { align-items: flex-start; }
        .audit-unit-summary { align-items: flex-end; flex-direction: column; gap: 0.15rem; }
    }

    @media (max-width: 575.98px) {
        .audit-unit-list { padding: 0.5rem; gap: 0.65rem; }
        .audit-groups-grid { grid-template-columns: 1fr; gap: 0.5rem; padding: 0.5rem; }
        .audit-group-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .audit-unit-note { grid-template-columns: 1fr; gap: 0.25rem; padding: 0.5rem; }
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
            <div class="alert alert-success shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <!-- Toolbar -->
        <div class="audit-toolbar mb-3">
            <div class="row g-3 align-items-center">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                        <span class="status-pill is-idle" id="save-status">
                            <i class="fa-solid fa-circle-check"></i><span>Siap</span>
                        </span>

                        <?php if ($editable): ?>
                            <form id="quick-form" method="post" action="<?= base_url('audit-andon/isi-cepat') ?>"
                                  class="d-flex gap-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
                                <input type="hidden" name="line" value="<?= (int) $line ?>">
                                <select name="field" class="form-select form-select-sm" style="max-width: 240px;">
                                    <option value="all">Semua kolom kosong</option>
                                    <?php foreach ($flat as $key => $def): ?>
                                        <option value="<?= $key ?>"><?= esc($def['group'] . ' · ' . $def['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-outline-success btn-sm fw-bold text-nowrap">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> ISI CEPAT
                                </button>
                            </form>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!$editable): ?>
                <div class="small text-secondary mt-2">Sesi terkunci. Data hanya bisa dilihat.</div>
            <?php endif; ?>
        </div>

        <!-- Grid pemeriksaan per unit -->
        <div class="audit-card">
            <div class="audit-unit-list" id="audit-unit-list">
                <?php foreach ($units as $u): ?>
                    <section class="audit-unit-card" id="unit-<?= (int) $u['id'] ?>"
                             data-unit="<?= (int) $u['id'] ?>" data-state="empty"
                             data-audit-filled="0" data-audit-bad="0">
                        <div class="audit-unit-head">
                            <div class="audit-unit-identity">
                                <span class="audit-unit-caption">UNIT</span>
                                <strong class="audit-unit-number"><?= (int) $u['line_no'] ?></strong>
                            </div>
                            <div class="audit-unit-summary">
                                <span class="audit-unit-state">Belum diisi</span>
                                <span class="audit-unit-count">0 / <?= (int) $fieldCount ?> pemeriksaan</span>
                            </div>
                        </div>

                        <div class="audit-groups-grid">
                            <?php $gIdx = 0; foreach ($groups as $gName => $fields): ?>
                                <section class="audit-group-panel">
                                    <div class="audit-group-head group-tone-<?= (int) $gIdx ?>">
                                        <i class="fa-solid <?= $groupIcons[$gName] ?? 'fa-circle' ?>"></i>
                                        <span><?= esc($gName) ?></span>
                                    </div>

                                    <div class="audit-group-fields">
                                        <?php foreach ($fields as $key => $def): ?>
                                            <div class="audit-field cell-empty" data-field="<?= $key ?>"
                                                 data-audit-filled="0" data-audit-bad="0">
                                                <div class="audit-field-title"><?= esc($def['label']) ?></div>
                                                <div class="btn-group btn-group-sm audit-choice-group" role="group">
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
                                                            <i class="fa-solid <?= $good ? 'fa-check' : 'fa-xmark' ?>"></i>
                                                            <?= esc($value) ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php $gIdx++; endforeach; ?>
                        </div>

                        <div class="audit-unit-note">
                            <label for="note-unit-<?= (int) $u['id'] ?>">
                                <i class="fa-solid fa-comment-dots me-1"></i> Catatan Unit
                            </label>
                            <input type="text" class="form-control form-control-sm"
                                   id="note-unit-<?= (int) $u['id'] ?>"
                                   data-unit="<?= (int) $u['id'] ?>" data-field="catatan"
                                   value="<?= esc($u['catatan'] ?? '') ?>"
                                   placeholder="Tambahkan catatan bila diperlukan"
                                   <?= $editable ? '' : 'disabled' ?>>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
            <?php if ($editable): ?>
                <div class="card-footer small text-secondary d-flex justify-content-between flex-wrap gap-2">
                    <span><i class="fa-solid fa-cloud-arrow-up me-1"></i> Perubahan tersimpan otomatis.</span>
                    <span><i class="fa-solid fa-circle-check text-success me-1"></i> Normal
                        <i class="fa-solid fa-circle-xmark text-danger ms-2 me-1"></i> Perlu perhatian
                        <i class="fa-solid fa-circle text-secondary ms-2 me-1"></i> Belum diisi
                    </span>
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

    const unitList = document.getElementById('audit-unit-list');
    const rows = unitList ? Array.from(unitList.querySelectorAll('.audit-unit-card[data-unit]')) : [];
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

    function setCellState(field, selected) {
        const filled = !!selected;
        const bad = filled && selected.dataset.bad === '1';

        field.classList.toggle('cell-ok', filled && !bad);
        field.classList.toggle('cell-bad', bad);
        field.classList.toggle('cell-empty', !filled);
        field.dataset.auditFilled = filled ? '1' : '0';
        field.dataset.auditBad = bad ? '1' : '0';

        return { filled: filled, bad: bad };
    }

    function setRowState(row, filled, badCount) {
        let state = 'empty';
        let label = 'Belum diisi';

        if (badCount > 0) {
            state = 'bad';
            label = 'Perlu perhatian';
        } else if (filled === CFG.fieldCount) {
            state = 'ok';
            label = 'Lengkap';
        } else if (filled > 0) {
            state = 'partial';
            label = 'Belum lengkap';
        }

        row.dataset.auditFilled = String(filled);
        row.dataset.auditBad = String(badCount);
        row.dataset.state = state;

        const stateEl = row.querySelector('.audit-unit-state');
        const countEl = row.querySelector('.audit-unit-count');
        if (stateEl) stateEl.textContent = label;
        if (countEl) countEl.textContent = filled + ' / ' + CFG.fieldCount + ' pemeriksaan';
    }

    // Inisialisasi status saat halaman dibuka.
    rows.forEach(function (row) {
        let filled = 0;
        let badCount = 0;

        row.querySelectorAll('.audit-field[data-field]').forEach(function (field) {
            const selected = field.querySelector('input.audit-radio:checked');
            const state = setCellState(field, selected);
            if (state.filled) filled++;
            if (state.bad) badCount++;
        });

        setRowState(row, filled, badCount);
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
            if (inFlight === 0) setStatus('saved', 'Tersimpan ' + data.time);
        } catch (e) {
            inFlight--;
            setStatus('error', 'Gagal menyimpan: ' + e.message);
        }
    }

    // Satu listener untuk pilihan audit dan catatan seluruh unit.
    if (unitList) {
        unitList.addEventListener('change', function (event) {
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) return;

            if (target.matches('input.audit-radio')) {
                const row = target.closest('.audit-unit-card[data-unit]');
                const field = target.closest('.audit-field[data-field]');
                if (!row || !field) return;

                const wasFilled = field.dataset.auditFilled === '1';
                const wasBad = field.dataset.auditBad === '1';
                const isBad = target.dataset.bad === '1';

                let filled = Number(row.dataset.auditFilled || 0);
                let badCount = Number(row.dataset.auditBad || 0);

                if (!wasFilled) filled++;
                if (wasBad) badCount--;
                if (isBad) badCount++;

                setCellState(field, target);
                setRowState(row, filled, badCount);

                if (CFG.editable) {
                    autosave({
                        unit_id: target.dataset.unit,
                        field: target.dataset.field,
                        value: target.value
                    });
                }
                return;
            }

            if (target.matches('input[data-field="catatan"]') && CFG.editable) {
                autosave({
                    unit_id: target.dataset.unit,
                    field: 'catatan',
                    value: target.value
                });
            }
        });
    }

    // Isi cepat: jumlahkan kotak yang masih kosong tanpa memeriksa tiap radio ulang.
    const quickForm = document.getElementById('quick-form');
    if (quickForm) {
        quickForm.addEventListener('submit', function (e) {
            if (inFlight > 0) {
                e.preventDefault();
                alert('Tunggu sampai penyimpanan selesai, lalu coba lagi.');
                return;
            }

            const selectedField = quickForm.elements['field'].value;
            let emptyCount = 0;

            rows.forEach(function (row) {
                row.querySelectorAll('.audit-field[data-field]').forEach(function (field) {
                    const matches = selectedField === 'all' || field.dataset.field === selectedField;
                    if (matches && field.dataset.auditFilled !== '1') emptyCount++;
                });
            });

            if (emptyCount === 0) {
                e.preventDefault();
                alert('Tidak ada kotak kosong untuk pilihan ini.');
                return;
            }

            const confirmed = confirm(
                'Isi ' + emptyCount + ' kotak kosong dengan nilai normal?\n\n' +
                'Kotak yang sudah diisi tidak akan berubah.'
            );
            if (!confirmed) e.preventDefault();
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
