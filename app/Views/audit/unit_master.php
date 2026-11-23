<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .audit-unit-summary {
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        background: var(--bs-body-bg);
        height: 100%;
    }

    .audit-unit-summary .summary-label {
        color: var(--bs-secondary-color);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .audit-unit-summary .summary-value {
        color: var(--bs-body-color);
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
    }

    .audit-unit-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        overflow: hidden;
    }

    .audit-unit-card .card-header {
        background: var(--bs-body-bg);
    }

    .audit-unit-table thead th {
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .audit-unit-table td {
        vertical-align: middle;
    }

    .audit-unit-table tbody tr:nth-child(even) {
        background: rgba(108, 117, 125, 0.045);
    }

    .audit-unit-table .form-control {
        max-width: 110px;
    }

    @media (min-width: 1200px) {
        .audit-unit-content {
            max-width: 1250px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <div class="text-secondary small text-uppercase fw-semibold">Settings · Audit Andon</div>
                <h1 class="mb-0 fs-3">MASTER BUTTON AUDIT</h1>
                <div class="small text-secondary">Atur jumlah button untuk setiap line sesuai kondisi aktual.</div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid audit-unit-content">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-4">
                <div class="audit-unit-summary">
                    <div class="summary-label">Jumlah Line</div>
                    <div class="summary-value"><?= (int) $totalLines ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="audit-unit-summary">
                    <div class="summary-label">Total Button</div>
                    <div class="summary-value"><?= (int) $totalUnits ?></div>
                </div>
            </div>
        </div>

        <div class="card audit-unit-card shadow-sm mb-3">
            <div class="card-header py-3">
                <h3 class="card-title fw-bold mb-1">ATUR JUMLAH BUTTON</h3>
                <div class="small text-secondary mt-1">
                    Bisa mengatur satu line atau rentang beberapa line sekaligus. Contoh: LINE 1–33 masing-masing 17 button.
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('audit-andon/master-button/simpan') ?>" id="audit-unit-bulk-form">
                    <?= csrf_field() ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold" for="line_from">Dari Line</label>
                            <input type="number" class="form-control" id="line_from" name="line_from"
                                   min="1" max="999" value="<?= esc(old('line_from', '1')) ?>" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold" for="line_to">Sampai Line</label>
                            <input type="number" class="form-control" id="line_to" name="line_to"
                                   min="1" max="999" value="<?= esc(old('line_to', '33')) ?>" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold" for="button_count">Jumlah Button per Line</label>
                            <input type="number" class="form-control" id="button_count" name="button_count"
                                   min="1" max="200" value="<?= esc(old('button_count', '17')) ?>" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <button type="submit" class="btn btn-success fw-bold w-100">
                                <i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN PENGATURAN
                            </button>
                        </div>
                    </div>
                </form>

                <div class="small text-secondary mt-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Jika jumlah dikurangi, nomor button di atas jumlah baru akan dihapus dari master. Riwayat audit yang sudah tersimpan tetap aman.
                </div>
            </div>
        </div>

        <div class="card audit-unit-card shadow-sm">
            <div class="card-header py-3">
                <h3 class="card-title fw-bold mb-0">DAFTAR JUMLAH BUTTON PER LINE</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 audit-unit-table">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 90px;">LINE</th>
                                <th>JUMLAH BUTTON SAAT INI</th>
                                <th style="width: 190px;">ATUR JUMLAH</th>
                                <th class="text-end pe-3" style="width: 130px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lines)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">
                                        Belum ada master button. Gunakan form di atas untuk membuatnya.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lines as $row): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold">LINE <?= (int) $row['line'] ?></td>
                                        <td>
                                            <span class="badge text-bg-primary"><?= (int) $row['total'] ?> BUTTON</span>
                                        </td>
                                        <td>
                                            <form method="post"
                                                  action="<?= base_url('audit-andon/master-button/simpan') ?>"
                                                  id="audit-unit-line-<?= (int) $row['line'] ?>"
                                                  class="d-flex align-items-center gap-2">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="line_from" value="<?= (int) $row['line'] ?>">
                                                <input type="hidden" name="line_to" value="<?= (int) $row['line'] ?>">
                                                <input type="number" name="button_count" class="form-control form-control-sm"
                                                       min="1" max="200" value="<?= (int) $row['total'] ?>" required>
                                            </form>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="submit" form="audit-unit-line-<?= (int) $row['line'] ?>"
                                                    class="btn btn-outline-success btn-sm fw-bold">
                                                <i class="fa-solid fa-check me-1"></i> SIMPAN
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="small text-secondary mt-3">
            Perubahan master digunakan saat sesi audit baru dibuat. Data sesi audit lama tidak diubah atau dihapus.
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const forms = document.querySelectorAll('#audit-unit-bulk-form, [id^="audit-unit-line-"]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const lineFrom = Number(form.querySelector('[name="line_from"]').value);
            const lineTo = Number(form.querySelector('[name="line_to"]').value);
            const count = Number(form.querySelector('[name="button_count"]').value);

            if (lineFrom < 1 || lineTo < lineFrom || lineTo - lineFrom + 1 > 100 || count < 1 || count > 200) {
                event.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Periksa Pengaturan',
                        text: 'Rentang maksimal 100 line dan jumlah button 1 sampai 200.',
                        confirmButtonColor: '#198754'
                    });
                }
                return;
            }

            event.preventDefault();
            const lineText = lineFrom === lineTo
                ? 'LINE ' + lineFrom
                : 'LINE ' + lineFrom + ' sampai ' + lineTo;
            const message = 'Atur ' + lineText + ' menjadi ' + count + ' button per line?';

            if (typeof Swal === 'undefined') {
                if (window.confirm(message)) form.submit();
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Simpan Pengaturan Button?',
                text: message + ' Master button akan diperbarui, sedangkan riwayat audit lama tetap aman.',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
})();
</script>
<?= $this->endSection() ?>
