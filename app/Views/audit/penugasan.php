<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .pic-line-picker {
        padding: 0.85rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        background: var(--bs-tertiary-bg);
    }

    .pic-line-picker-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        margin-bottom: 0.75rem;
    }

    .pic-line-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(104px, 1fr));
        gap: 0.45rem;
        max-height: 300px;
        overflow-y: auto;
        padding: 2px;
    }

    .pic-line-option {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-height: 39px;
        padding: 0.45rem 0.55rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 7px;
        background: var(--bs-body-bg);
        cursor: pointer;
        user-select: none;
    }

    .pic-line-option:has(input:checked) {
        border-color: var(--bs-success);
        background: rgba(25, 135, 84, 0.09);
        box-shadow: inset 0 0 0 1px rgba(25, 135, 84, 0.18);
    }

    .pic-line-option.is-assigned {
        background: var(--bs-secondary-bg);
        opacity: 0.68;
        cursor: not-allowed;
    }

    .pic-line-option .form-check-input {
        margin: 0;
        flex: 0 0 auto;
    }

    .pic-line-option .line-name {
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .pic-line-option .line-owner {
        margin-left: auto;
        font-size: 0.58rem;
        color: var(--bs-secondary-color);
        text-align: right;
    }

    .pic-line-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem;
        font-size: 0.72rem;
        color: var(--bs-secondary-color);
    }

    .pic-line-legend span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pic-line-legend .legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 3px;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
    }

    .pic-line-legend .legend-dot.selected {
        border-color: var(--bs-success);
        background: rgba(25, 135, 84, 0.22);
    }

    .pic-line-legend .legend-dot.assigned {
        background: var(--bs-secondary-bg);
    }

    @media (max-width: 575.98px) {
        .pic-line-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
</style>
<?= $this->endSection() ?>



<?= $this->section('content') ?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">PENUGASAN PIC AUDIT</h1>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= base_url('audit-andon') ?>" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> AUDIT ANDON
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

        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title fw-bold text-dark mb-0">Tambah Penugasan</h3>
            </div>
            <div class="card-body">
                <?php if (empty($pics)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada user dengan role PIC Audit. Buat dulu lewat menu User Management.
                    </div>
                <?php else: ?>
                    <?php
                        $oldSelectedLines = old('lines');
                        if (!is_array($oldSelectedLines)) {
                            $oldSelectedLines = [];
                        }
                        $oldSelectedLines = array_map('intval', $oldSelectedLines);
                    ?>
                    <form id="pic-assignment-form" method="post" action="<?= base_url('audit-andon/penugasan/simpan') ?>" class="row g-3">
                        <?= csrf_field() ?>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">PIC</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Pilih PIC</option>
                                <?php foreach ($pics as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>" <?= (int) old('user_id') === (int) $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['full_name'] ?: $p['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <div>
                                    <label class="form-label fw-bold small mb-0">Pilih Line</label>
                                    <div class="small text-secondary">Pilih satu atau beberapa line. Tidak harus berurutan.</div>
                                </div>
                                <span class="badge text-bg-success" id="pic-line-selected-count">0 LINE DIPILIH</span>
                            </div>

                            <div class="pic-line-picker">
                                <div class="pic-line-picker-toolbar">
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-success btn-sm fw-bold" id="pic-line-select-all">
                                            <i class="fa-solid fa-check-double me-1"></i> PILIH SEMUA TERSEDIA
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" id="pic-line-clear">
                                            BERSIHKAN
                                        </button>
                                    </div>
                                    <div class="pic-line-legend">
                                        <span><i class="legend-dot selected"></i> Dipilih</span>
                                        <span><i class="legend-dot assigned"></i> Sudah ditugaskan</span>
                                    </div>
                                </div>

                                <?php if (empty($lineOptions)): ?>
                                    <div class="text-secondary small py-2">Belum ada data line pada master unit Audit Andon.</div>
                                <?php else: ?>
                                    <div class="pic-line-grid" id="pic-line-grid">
                                        <?php foreach ($lineOptions as $line): ?>
                                            <?php
                                                $assignedUserId = $assignedByLine[$line] ?? null;
                                                $assignedName = $assignedUserId !== null
                                                    ? ($picNames[$assignedUserId] ?? 'PIC lain')
                                                    : '';
                                            ?>
                                            <label class="pic-line-option<?= $assignedUserId !== null ? ' is-assigned' : '' ?>">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input pic-line-checkbox"
                                                    name="lines[]"
                                                    value="<?= (int) $line ?>"
                                                    <?= $assignedUserId === null && in_array((int) $line, $oldSelectedLines, true) ? 'checked' : '' ?>
                                                    <?= $assignedUserId !== null ? 'disabled' : '' ?>>
                                                <span class="line-name">LINE <?= (int) $line ?></span>
                                                <?php if ($assignedUserId !== null): ?>
                                                    <small class="line-owner"><?= esc($assignedName) ?></small>
                                                <?php endif; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success fw-bold shadow-sm px-4">
                                <i class="fa-solid fa-plus me-1"></i> SIMPAN PENUGASAN
                            </button>
                        </div>
                    </form>             </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title fw-bold text-dark mb-0">Daftar Penugasan</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>PIC</th>
                                <th>Line yang Ditugaskan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pics)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">Belum ada PIC Audit.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($pics as $p): ?>
                                <?php $rows = $ranges[(int) $p['id']] ?? []; ?>
                                <?php $nama = esc($p['full_name'] ?: $p['username']); ?>

                                <?php if (empty($rows)): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= $nama ?></td>
                                        <td class="text-secondary">Belum ada penugasan</td>
                                        <td></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= $nama ?></td>
                                            <td>
                                                <span class="badge text-bg-info">
                                                    <?php if ((int) $r['line_from'] === (int) $r['line_to']): ?>
                                                        LINE <?= (int) $r['line_from'] ?>
                                                    <?php else: ?>
                                                        LINE <?= (int) $r['line_from'] ?> – <?= (int) $r['line_to'] ?>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <form method="post"
                                                      action="<?= base_url('audit-andon/penugasan/hapus/' . (int) $r['id']) ?>"
                                                      onsubmit="return confirm('Hapus penugasan ini?')"
                                                      class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-outline-danger btn-sm fw-bold shadow-sm">
                                                        <i class="fa-solid fa-trash-can me-1"></i> HAPUS
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="small text-secondary mt-3">
            Setiap line dipilih dan ditugaskan secara terpisah. Line yang sudah menjadi penugasan PIC lain tidak dapat dipilih kembali.
            Mengubah penugasan tidak mengubah sesi yang sudah dibuat; perubahan berlaku saat PIC membuka form berikutnya.
        </div>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const grid = document.getElementById('pic-line-grid');
    if (!grid) return;

    const checkboxes = Array.from(grid.querySelectorAll('.pic-line-checkbox'));
    const countEl = document.getElementById('pic-line-selected-count');
    const selectAllBtn = document.getElementById('pic-line-select-all');
    const clearBtn = document.getElementById('pic-line-clear');
    const form = document.getElementById('pic-assignment-form');

    function updateCount() {
        const selected = checkboxes.filter(function (checkbox) {
            return checkbox.checked && !checkbox.disabled;
        }).length;

        countEl.textContent = selected + ' LINE DIPILIH';
    }

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateCount);
    });

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            checkboxes.forEach(function (checkbox) {
                if (!checkbox.disabled) checkbox.checked = true;
            });
            updateCount();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });
            updateCount();
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            const selected = checkboxes.some(function (checkbox) {
                return checkbox.checked && !checkbox.disabled;
            });

            if (!selected) {
                event.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Line',
                        text: 'Pilih minimal satu line untuk PIC ini.',
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Pilih minimal satu line untuk PIC ini.');
                }
            }
        });
    }

    updateCount();
})();
</script>
<?= $this->endSection() ?>
