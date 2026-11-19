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
        padding: 0.5rem 0.6rem;
    }

    .audit-table thead tr:nth-child(1) th {
        position: sticky;
        top: 0;
        z-index: 3;
        height: 42px;
        background-color: var(--bs-tertiary-bg);
        border-top: 0;
    }

    .audit-table thead tr:nth-child(2) th {
        position: sticky;
        top: 42px;
        z-index: 3;
        height: 38px;
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
        background-image: repeating-linear-gradient(45deg, transparent 0 6px, rgba(128, 128, 128, 0.18) 6px 12px);
    }

    .audit-table .sticky-col {
        position: sticky;
        left: 0;
        z-index: 2;
        border-right: 2px solid var(--bs-border-color);
        min-width: 112px;
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
        min-width: 66px;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 0.3rem 0.5rem;
        white-space: nowrap;
    }

    .audit-table .btn-group .btn i {
        font-size: 0.65rem;
        margin-right: 0.25rem;
    }

    /* ===== Indikator 13 kolom per unit ===== */
    .seg-bar {
        display: flex;
        gap: 2px;
        margin-top: 0.4rem;
    }

    .seg-bar span {
        width: 5px;
        height: 10px;
        border-radius: 2px;
        background-color: var(--bs-secondary-bg);
        transition: background-color 0.2s;
    }

    .seg-bar span.seg-ok  { background-color: var(--bs-success); }
    .seg-bar span.seg-bad { background-color: var(--bs-danger); }

    .unit-no {
        font-size: 0.95rem;
    }

    .unit-count {
        font-size: 0.7rem;
        color: var(--bs-secondary-color);
        font-variant-numeric: tabular-nums;
        margin-top: 0.1rem;
    }

    .note-input {
        min-width: 200px;
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
                                        class="text-center grp-head<?= $gIdx % 2 ? ' grp-alt' : '' ?><?= $gIdx > 0 ? ' grp-start' : '' ?>">
                                        <i class="fa-solid <?= $groupIcons[$gName] ?? 'fa-circle' ?>"></i><?= esc($gName) ?>
                                    </th>
                                    <?php $gIdx++; ?>
                                <?php endforeach; ?>
                                <th rowspan="2" class="col-label text-start grp-start">Catatan</th>
                            </tr>
                            <tr>
                                <?php foreach ($flat as $def): ?>
                                    <th class="text-center col-label<?= $def['start'] ? ' grp-start' : '' ?><?= $def['grp'] % 2 ? ' grp-alt' : '' ?>"><?= esc($def['label']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($units as $u): ?>
                                <tr id="unit-<?= (int) $u['id'] ?>" data-unit="<?= (int) $u['id'] ?>" data-state="empty">
                                    <td class="sticky-col bg-body">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="unit-no fw-bold">#<?= (int) $u['line_no'] ?></span>
                                            <span class="led-dot text-secondary" id="led-<?= (int) $u['id'] ?>"></span>
                                        </div>
                                        <div class="seg-bar" id="seg-<?= (int) $u['id'] ?>">
                                            <?php foreach ($flat as $key => $def): ?>
                                                <span data-seg="<?= $key ?>"></span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="unit-count" id="cnt-<?= (int) $u['id'] ?>">0 / <?= $fieldCount ?></div>
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

                                    <td class="grp-start">
                                        <div class="input-group input-group-sm note-input">
                                            <span class="input-group-text text-secondary"><i class="fa-solid fa-comment-dots"></i></span>
                                            <input type="text" class="form-control"
                                                   data-unit="<?= (int) $u['id'] ?>" data-field="catatan"
                                                   value="<?= esc($u['catatan'] ?? '') ?>"
                                                   placeholder="Tulis catatan"
                                                   <?= $editable ? '' : 'disabled' ?>>
                                        </div>
                                    </td>
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

    const rows = Array.from(document.querySelectorAll('tr[data-unit]'));
    const statusEl = document.getElementById('save-status');
    const progText = document.getElementById('prog-text');
    const progBar  = document.getElementById('prog-bar');

    const LED  = { bad: 'text-danger', ok: 'text-success', partial: 'text-warning', empty: 'text-secondary' };
    const PILL = { idle: 'is-idle', saving: 'is-saving', saved: 'is-saved', error: 'is-error' };
    const ICON = {
        idle:   'fa-solid fa-circle-check',
        saving: 'fa-solid fa-rotate fa-spin',
        saved:  'fa-solid fa-circle-check',
        error:  'fa-solid fa-triangle-exclamation',
    };

    let inFlight = 0;

    function setStatus(kind, text) {
        statusEl.className = 'status-pill ' + PILL[kind];
        statusEl.querySelector('i').className = ICON[kind];
        statusEl.querySelector('span').textContent = text;
    }

    function refreshRow(tr) {
        const id = tr.dataset.unit;
        const dot = document.getElementById('led-' + id);
        const cnt = document.getElementById('cnt-' + id);
        const seg = document.getElementById('seg-' + id);
        let filled = 0;
        let bad = false;

        tr.querySelectorAll('td[data-field]').forEach(td => {
            const key = td.dataset.field;
            const bar = seg.querySelector('[data-seg="' + key + '"]');
            td.classList.remove('cell-ok', 'cell-bad', 'cell-empty');
            bar.classList.remove('seg-ok', 'seg-bad');

            const checked = td.querySelector('input.audit-radio:checked');
            if (!checked) {
                td.classList.add('cell-empty');
                return;
            }

            filled++;
            if (checked.dataset.bad === '1') {
                td.classList.add('cell-bad');
                bar.classList.add('seg-bad');
                bad = true;
            } else {
                td.classList.add('cell-ok');
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

        tr.dataset.state = state;
        cnt.textContent = filled + ' / ' + CFG.fieldCount + ' terisi';

        Object.values(LED).forEach(c => dot.classList.remove(c));
        dot.classList.remove('is-active');
        dot.classList.add(LED[state]);
        if (state !== 'empty') {
            dot.classList.add('is-active');
        }
    }

    function refreshToolbar() {
        let ok = 0;
        rows.forEach(tr => {
            if (tr.dataset.state === 'ok') {
                ok++;
            }
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
            if (data.csrf) {
                CFG.csrfHash = data.csrf;
            }
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

    rows.forEach(tr => {
        tr.querySelectorAll('input.audit-radio').forEach(r => {
            r.addEventListener('change', () => {
                refreshRow(tr);
                refreshToolbar();
                if (CFG.editable) {
                    autosave({ unit_id: r.dataset.unit, field: r.dataset.field, value: r.value });
                }
            });
        });

        tr.querySelectorAll('input[data-field="catatan"]').forEach(inp => {
            inp.addEventListener('change', () => {
                if (CFG.editable) {
                    autosave({ unit_id: inp.dataset.unit, field: 'catatan', value: inp.value });
                }
            });
        });

        refreshRow(tr);
    });
    refreshToolbar();

    // Isi cepat: hitung kotak kosong dulu, lalu minta konfirmasi
    const quickForm = document.getElementById('quick-form');
    if (quickForm) {
        quickForm.addEventListener('submit', function (e) {
            if (inFlight > 0) {
                e.preventDefault();
                alert('Tunggu sampai penyimpanan selesai, lalu coba lagi.');
                return;
            }

            const field = quickForm.elements['field'].value;
            let kosong = 0;
            rows.forEach(tr => {
                tr.querySelectorAll('td[data-field]').forEach(td => {
                    const cocok = field === 'all' || td.dataset.field === field;
                    if (cocok && !td.querySelector('input.audit-radio:checked')) {
                        kosong++;
                    }
                });
            });

            if (kosong === 0) {
                e.preventDefault();
                alert('Tidak ada kotak kosong untuk pilihan ini.');
                return;
            }

            const ok = confirm(
                'Isi ' + kosong + ' kotak kosong dengan nilai normal?\n\n' +
                'Kotak yang sudah diisi tidak akan berubah.'
            );
            if (!ok) {
                e.preventDefault();
            }
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
