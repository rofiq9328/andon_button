<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<?= $this->include('audit/_styles') ?>
<style>
    /* Form audit per unit: lebih lapang dan mudah dibaca */
    .audit-toolbar {
        padding: 0.7rem 0.85rem;
    }

    .audit-toolbar .progress-thin {
        height: 7px;
    }

    .audit-toolbar .legend-chip,
    .audit-toolbar .status-pill {
        font-size: 0.75rem;
    }

    .audit-toolbar .btn-sm,
    .audit-toolbar .form-select-sm {
        font-size: 0.78rem;
    }

    .audit-unit-list {
        display: grid;
        gap: 0.75rem;
    }

    .audit-unit-card {
        border: 1px solid var(--bs-border-color);
        border-left: 4px solid var(--bs-secondary);
        border-radius: 13px !important;
        overflow: hidden;
        background: var(--bs-body-bg);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .audit-unit-card[data-state="ok"] {
        border-left-color: var(--bs-success);
    }

    .audit-unit-card[data-state="bad"] {
        border-left-color: var(--bs-danger);
    }

    .audit-unit-card[data-state="partial"] {
        border-left-color: var(--bs-warning);
    }

    .audit-unit-card .accordion-button {
        padding: 0.9rem 1rem;
        gap: 0.8rem;
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
        box-shadow: none;
    }

    .audit-unit-card .accordion-button:not(.collapsed) {
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
        border-bottom: 1px solid var(--bs-border-color);
    }

    .audit-unit-card .accordion-button::after {
        margin-left: 0.35rem;
        flex-shrink: 0;
    }

    .unit-heading-content {
        flex: 1;
        min-width: 0;
    }

    .unit-heading-top {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .unit-no {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .state-badge {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.32rem 0.55rem;
    }

    .unit-summary-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.55rem;
        color: var(--bs-secondary-color);
        font-size: 0.74rem;
    }

    .unit-summary-meta .led-dot {
        width: 8px;
        height: 8px;
    }

    .seg-bar {
        display: flex;
        gap: 3px;
        margin-top: 0.45rem;
        max-width: 300px;
    }

    .seg-bar span {
        flex: 1;
        min-width: 5px;
        height: 5px;
        border-radius: 99px;
        background: var(--bs-secondary-bg);
    }

    .seg-bar span.seg-ok {
        background: var(--bs-success);
    }

    .seg-bar span.seg-bad {
        background: var(--bs-danger);
    }

    .audit-unit-card .accordion-body {
        padding: 1rem;
        background: var(--bs-tertiary-bg);
    }

    .unit-fields-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .audit-group-card {
        min-width: 0;
        padding: 0.8rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 11px;
        background: var(--bs-body-bg);
    }

    .audit-group-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.6rem;
        margin-bottom: 0.65rem;
        border-bottom: 1px solid var(--bs-border-color);
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .audit-group-title i {
        color: var(--bs-secondary-color);
        width: 1rem;
        text-align: center;
    }

    .audit-group-fields {
        display: grid;
        gap: 0.7rem;
    }

    .audit-field {
        padding: 0.45rem;
        border: 1px solid transparent;
        border-radius: 9px;
        transition: background 0.15s, border-color 0.15s;
    }

    .audit-field.cell-ok {
        background: rgba(25, 135, 84, 0.07);
        border-color: rgba(25, 135, 84, 0.18);
    }

    .audit-field.cell-bad {
        background: rgba(220, 53, 69, 0.07);
        border-color: rgba(220, 53, 69, 0.2);
    }

    .audit-field.cell-empty {
        background: var(--bs-tertiary-bg);
        border-color: var(--bs-border-color);
    }

    .audit-field-label {
        margin-bottom: 0.4rem;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--bs-body-color);
    }

    .audit-choice-group {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        width: 100%;
        gap: 0.35rem;
    }

    .audit-choice-group .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        min-height: 38px;
        padding: 0.35rem 0.4rem;
        border-radius: 7px !important;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .audit-choice-group .btn i {
        font-size: 0.68rem;
    }

    .audit-choice-group .btn-check:checked + .btn-outline-success {
        color: #fff;
        background: var(--bs-success);
        border-color: var(--bs-success);
    }

    .audit-choice-group .btn-check:checked + .btn-outline-danger {
        color: #fff;
        background: var(--bs-danger);
        border-color: var(--bs-danger);
    }

    .audit-note-card {
        grid-column: 1 / -1;
        padding: 0.8rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 11px;
        background: var(--bs-body-bg);
    }

    .audit-note-card label {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .audit-note-card .form-control {
        min-height: 40px;
        font-size: 0.82rem;
    }

    .audit-form-footer {
        padding: 0.75rem 1rem;
        border-top: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        color: var(--bs-secondary-color);
        font-size: 0.74rem;
    }

    @media (max-width: 1199.98px) {
        .unit-fields-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .audit-toolbar {
            position: static;
        }

        .audit-unit-card .accordion-button {
            padding: 0.75rem;
        }

        .audit-unit-card .accordion-body {
            padding: 0.65rem;
        }

        .unit-fields-grid {
            grid-template-columns: 1fr;
            gap: 0.6rem;
        }

        .audit-group-card {
            padding: 0.7rem;
        }

        .audit-choice-group .btn {
            min-height: 42px;
            font-size: 0.76rem;
        }

        .seg-bar {
            max-width: 100%;
        }
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
                <div class="col-lg-4">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">Progres line</span>
                        <span class="text-secondary" id="prog-text">0 dari <?= $lineTotal ?> unit lengkap</span>
                    </div>
                    <div class="progress-thin progress">
                        <div class="progress-bar bg-success" id="prog-bar" style="width: 0%"></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-3">
                        <span class="legend-chip"><span class="led-dot text-secondary"></span>Belum</span>
                        <span class="legend-chip"><span class="led-dot text-warning"></span>Sebagian</span>
                        <span class="legend-chip"><span class="led-dot text-success"></span>Lengkap</span>
                        <span class="legend-chip"><span class="led-dot text-danger"></span>Perlu perhatian</span>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
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

                            <a href="<?= base_url($base . '/review') ?>" class="btn btn-success btn-sm fw-bold text-nowrap">
                                <i class="fa-solid fa-clipboard-check me-1"></i> TINJAU
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!$editable): ?>
                <div class="small text-secondary mt-2">Sesi terkunci. Data hanya bisa dilihat.</div>
            <?php endif; ?>
        </div>

        <!-- Form input per unit -->
        <div class="audit-unit-list" id="auditUnitAccordion">
            <?php foreach ($units as $unitIndex => $u): ?>
                <?php
                $unitFieldStates = [];
                $unitFilled = 0;
                $unitHasBad = false;

                foreach ($flat as $key => $def) {
                    $currentValue = $u[$key] ?? null;
                    $fieldState = 'empty';

                    if ($currentValue !== null && $currentValue !== '') {
                        $unitFilled++;
                        $fieldState = 'ok';

                        foreach ($def['opts'] as [$optionValue, $good]) {
                            if ($currentValue === $optionValue) {
                                $fieldState = $good ? 'ok' : 'bad';
                                if (!$good) {
                                    $unitHasBad = true;
                                }
                                break;
                            }
                        }
                    }

                    $unitFieldStates[$key] = $fieldState;
                }

                $unitState = $unitHasBad
                    ? 'bad'
                    : ($unitFilled === $fieldCount ? 'ok' : ($unitFilled > 0 ? 'partial' : 'empty'));

                $stateLabels = [
                    'bad' => ['Perlu perhatian', 'danger'],
                    'ok' => ['Lengkap', 'success'],
                    'partial' => ['Sebagian', 'warning'],
                    'empty' => ['Belum diisi', 'secondary'],
                ];

                [$stateLabel, $stateTone] = $stateLabels[$unitState];
                $unitOpen = $unitIndex === 0;
                $unitId = (int) $u['id'];
                $collapseId = 'unit-body-' . $unitId;
                $headingId = 'unit-heading-' . $unitId;
                ?>
                <div class="accordion-item audit-unit-card"
                     id="unit-<?= $unitId ?>"
                     data-unit="<?= $unitId ?>"
                     data-state="<?= $unitState ?>">
                    <h2 class="accordion-header" id="<?= $headingId ?>">
                        <button class="accordion-button<?= $unitOpen ? '' : ' collapsed' ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= $collapseId ?>"
                                aria-expanded="<?= $unitOpen ? 'true' : 'false' ?>"
                                aria-controls="<?= $collapseId ?>">
                            <div class="unit-heading-content">
                                <div class="unit-heading-top">
                                    <span class="unit-no">UNIT #<?= (int) $u['line_no'] ?></span>
                                    <span class="badge text-bg-<?= $stateTone ?> state-badge" id="state-<?= $unitId ?>"><?= esc($stateLabel) ?></span>
                                </div>
                                <div class="unit-summary-meta">
                                    <span class="led-dot <?= $unitState === 'bad' ? 'text-danger' : ($unitState === 'ok' ? 'text-success' : ($unitState === 'partial' ? 'text-warning' : 'text-secondary')) ?>"
                                          id="led-<?= $unitId ?>"></span>
                                    <span id="cnt-<?= $unitId ?>"><?= $unitFilled ?> / <?= $fieldCount ?> pemeriksaan terisi</span>
                                    <span>·</span>
                                    <span><?= $fieldCount ?> pemeriksaan</span>
                                </div>
                                <div class="seg-bar" id="seg-<?= $unitId ?>">
                                    <?php foreach ($flat as $key => $def): ?>
                                        <span data-seg="<?= $key ?>" class="<?= $unitFieldStates[$key] === 'ok' ? 'seg-ok' : ($unitFieldStates[$key] === 'bad' ? 'seg-bad' : '') ?>"></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </button>
                    </h2>

                    <div id="<?= $collapseId ?>"
                         class="accordion-collapse collapse<?= $unitOpen ? ' show' : '' ?>"
                         aria-labelledby="<?= $headingId ?>"
                         data-bs-parent="#auditUnitAccordion">
                        <div class="accordion-body">
                            <div class="unit-fields-grid">
                                <?php foreach ($groups as $gName => $fields): ?>
                                    <section class="audit-group-card">
                                        <div class="audit-group-title">
                                            <i class="fa-solid <?= $groupIcons[$gName] ?? 'fa-circle' ?>"></i>
                                            <span><?= esc($gName) ?></span>
                                        </div>

                                        <div class="audit-group-fields">
                                            <?php foreach ($fields as $key => $def): ?>
                                                <?php $currentValue = $u[$key] ?? null; ?>
                                                <div class="audit-field cell-<?= $unitFieldStates[$key] ?>" data-field="<?= $key ?>">
                                                    <div class="audit-field-label"><?= esc($def['label']) ?></div>
                                                    <div class="audit-choice-group" role="group" aria-label="<?= esc($gName . ' ' . $def['label']) ?>">
                                                        <?php foreach ($def['opts'] as $i => [$value, $good]): ?>
                                                            <?php $rid = 'u' . $unitId . '_' . $key . '_' . $i; ?>
                                                            <input type="radio"
                                                                   class="btn-check audit-radio"
                                                                   name="u<?= $unitId ?>_<?= $key ?>"
                                                                   id="<?= $rid ?>"
                                                                   value="<?= esc($value) ?>"
                                                                   data-unit="<?= $unitId ?>"
                                                                   data-field="<?= $key ?>"
                                                                   data-bad="<?= $good ? '0' : '1' ?>"
                                                                   autocomplete="off"
                                                                   <?= ($currentValue === $value) ? 'checked' : '' ?>
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
                                <?php endforeach; ?>

                                <div class="audit-note-card">
                                    <label for="note-<?= $unitId ?>">
                                        <i class="fa-solid fa-comment-dots text-secondary me-1"></i>
                                        Catatan unit <span class="fw-normal text-secondary">(opsional)</span>
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="note-<?= $unitId ?>"
                                           data-unit="<?= $unitId ?>"
                                           data-field="catatan"
                                           value="<?= esc($u['catatan'] ?? '') ?>"
                                           placeholder="Tulis catatan bila ada masalah atau kondisi khusus"
                                           <?= $editable ? '' : 'disabled' ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card audit-card mt-3">
            <?php if ($editable): ?>
                <div class="audit-form-footer d-flex justify-content-between flex-wrap gap-2">
                    <span><i class="fa-solid fa-cloud-arrow-up me-1"></i> Perubahan tersimpan otomatis.</span>
                    <span><i class="fa-solid fa-circle-info me-1"></i> Hijau = normal, merah = perlu perhatian, abu-abu = belum diisi.</span>
                </div>
            <?php else: ?>
                <div class="audit-form-footer">Sesi terkunci. Data hanya bisa dilihat.</div>
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

    const rows = Array.from(document.querySelectorAll('.audit-unit-card[data-unit]'));
    const statusEl = document.getElementById('save-status');
    const progText = document.getElementById('prog-text');
    const progBar  = document.getElementById('prog-bar');

    const LED = {
        bad: 'text-danger',
        ok: 'text-success',
        partial: 'text-warning',
        empty: 'text-secondary'
    };

    const STATE = {
        bad: { label: 'Perlu perhatian', badge: 'danger' },
        ok: { label: 'Lengkap', badge: 'success' },
        partial: { label: 'Sebagian', badge: 'warning' },
        empty: { label: 'Belum diisi', badge: 'secondary' }
    };

    const PILL = { idle: 'is-idle', saving: 'is-saving', saved: 'is-saved', error: 'is-error' };
    const ICON = {
        idle:   'fa-solid fa-circle-check',
        saving: 'fa-solid fa-rotate fa-spin',
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

    function refreshRow(card) {
        const id = card.dataset.unit;
        const dot = document.getElementById('led-' + id);
        const count = document.getElementById('cnt-' + id);
        const seg = document.getElementById('seg-' + id);
        const badge = document.getElementById('state-' + id);
        let filled = 0;
        let bad = false;

        card.querySelectorAll('.audit-field[data-field]').forEach(fieldWrap => {
            const key = fieldWrap.dataset.field;
            const bar = seg.querySelector('[data-seg="' + key + '"]');
            fieldWrap.classList.remove('cell-ok', 'cell-bad', 'cell-empty');
            bar.classList.remove('seg-ok', 'seg-bad');

            const checked = fieldWrap.querySelector('input.audit-radio:checked');
            if (!checked) {
                fieldWrap.classList.add('cell-empty');
                return;
            }

            filled++;
            if (checked.dataset.bad === '1') {
                fieldWrap.classList.add('cell-bad');
                bar.classList.add('seg-bad');
                bad = true;
            } else {
                fieldWrap.classList.add('cell-ok');
                bar.classList.add('seg-ok');
            }
        });

        let state;
        if (bad) {
            state = 'bad';
        } else if (filled === CFG.fieldCount) {
            state = 'ok';
        } else if (filled > 0) {
            state = 'partial';
        } else {
            state = 'empty';
        }

        card.dataset.state = state;
        count.textContent = filled + ' / ' + CFG.fieldCount + ' pemeriksaan terisi';

        if (badge) {
            badge.className = 'badge text-bg-' + STATE[state].badge + ' state-badge';
            badge.textContent = STATE[state].label;
            if (state === 'partial') badge.classList.add('text-dark');
        }

        Object.values(LED).forEach(className => dot.classList.remove(className));
        dot.classList.remove('is-active');
        dot.classList.add(LED[state]);
        if (state !== 'empty') dot.classList.add('is-active');
    }

    function refreshToolbar() {
        let ok = 0;
        rows.forEach(card => {
            if (card.dataset.state === 'ok') ok++;
        });
        const total = rows.length;
        progText.textContent = ok + ' dari ' + total + ' unit lengkap';
        progBar.style.width = (total ? (ok / total * 100) : 0) + '%';
    }

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
                setStatus('saved', 'Tersimpan ' + data.time);
            }
        } catch (e) {
            inFlight--;
            setStatus('error', 'Gagal menyimpan: ' + e.message);
        }
    }

    rows.forEach(card => {
        card.querySelectorAll('input.audit-radio').forEach(radio => {
            radio.addEventListener('change', () => {
                refreshRow(card);
                refreshToolbar();
                if (CFG.editable) {
                    autosave({
                        unit_id: radio.dataset.unit,
                        field: radio.dataset.field,
                        value: radio.value
                    });
                }
            });
        });

        card.querySelectorAll('input[data-field="catatan"]').forEach(input => {
            input.addEventListener('change', () => {
                if (CFG.editable) {
                    autosave({
                        unit_id: input.dataset.unit,
                        field: 'catatan',
                        value: input.value
                    });
                }
            });
        });

        refreshRow(card);
    });

    refreshToolbar();

    // Isi cepat hanya melengkapi pilihan yang masih kosong.
    const quickForm = document.getElementById('quick-form');
    if (quickForm) {
        quickForm.addEventListener('submit', function (event) {
            if (inFlight > 0) {
                event.preventDefault();
                alert('Tunggu sampai penyimpanan selesai, lalu coba lagi.');
                return;
            }

            const field = quickForm.elements['field'].value;
            let kosong = 0;

            rows.forEach(card => {
                card.querySelectorAll('.audit-field[data-field]').forEach(fieldWrap => {
                    const cocok = field === 'all' || fieldWrap.dataset.field === field;
                    if (cocok && !fieldWrap.querySelector('input.audit-radio:checked')) {
                        kosong++;
                    }
                });
            });

            if (kosong === 0) {
                event.preventDefault();
                alert('Tidak ada pemeriksaan kosong untuk pilihan ini.');
                return;
            }

            if (!confirm(
                'Isi ' + kosong + ' pemeriksaan kosong dengan nilai normal?\n\n' +
                'Pilihan yang sudah diisi tidak akan berubah.'
            )) {
                event.preventDefault();
            }
        });
    }

    window.addEventListener('beforeunload', function (event) {
        if (inFlight > 0) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
</script>
<?= $this->endSection() ?>
