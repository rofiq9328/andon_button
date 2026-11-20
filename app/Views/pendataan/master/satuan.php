<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .master-intro {
        color: var(--bs-secondary-color);
        font-size: .88rem;
    }

    .master-card {
        border: 1px solid var(--bs-border-color);
        border-top: 3px solid var(--bs-success);
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        overflow: hidden;
    }

    .master-card .card-header {
        background: var(--bs-body-bg);
        border-bottom: 1px solid var(--bs-border-color);
        padding: .85rem 1rem;
    }

    .master-table th {
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }

    .master-table td {
        vertical-align: middle;
        font-size: .86rem;
    }

    .unit-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 70px;
        padding: .32rem .55rem;
        border-radius: 7px;
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
        font-weight: 700;
        font-size: .78rem;
    }

    .unit-modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
    }

    .unit-modal .modal-header {
        padding: 1.1rem 1.35rem;
        background: #fff;
    }

    .unit-modal .modal-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #212529;
    }

    .unit-modal .modal-title-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #e8f5ee;
        color: #198754;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .unit-modal .modal-body {
        padding: 1.35rem;
    }

    .unit-modal .form-label {
        font-size: .88rem;
        font-weight: 600;
        color: #343a40;
    }

    .unit-modal .form-control:focus,
    .unit-modal .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .12);
    }

    .unit-modal .modal-footer {
        padding: .95rem 1.35rem;
        background: #fbfcfb;
    }

    @media (max-width: 575.98px) {
        .unit-search {
            max-width: none !important;
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors') ?? [];
$editUnitId = session()->getFlashdata('edit_satuan_id') ?? old('id_satuan');
$isAdmin = \App\Libraries\Roles::has('admin');
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-7">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Master Data</div>
                <h1 class="mb-1 fs-3">MASTER SATUAN</h1>
                <div class="master-intro">Kelola satuan ukuran yang digunakan pada pencatatan barang dan transaksi.</div>
            </div>
            <div class="col-md-5 d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="<?= base_url('pendataan') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> DASHBOARD
                </a>
                <?php if ($isAdmin): ?>
                    <button type="button" id="btnAddUnit" class="btn btn-success btn-sm fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#unitModal">
                        <i class="fa-solid fa-plus me-1"></i> TAMBAH SATUAN
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm">
                <div class="fw-bold mb-1">Periksa kembali isian berikut:</div>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card master-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="h6 fw-bold mb-0">Daftar Satuan</h2>
                    <div class="small text-secondary mt-1"><?= count($units) ?> satuan terdaftar</div>
                </div>
                <div class="input-group input-group-sm unit-search" style="max-width: 280px;">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                    <input type="search" id="searchUnit" class="form-control" placeholder="Cari kode atau nama satuan...">
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover master-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:65px;">No</th>
                                <th style="width:180px;">Kode Satuan</th>
                                <th>Nama Satuan</th>
                                <th class="text-center" style="width:115px;">Status</th>
                                <?php if ($isAdmin): ?>
                                    <th class="text-end" style="width:125px;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="unitTableBody">
                            <?php if (empty($units)): ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? 5 : 4 ?>" class="text-center text-secondary py-5">
                                        <i class="fa-solid fa-ruler-combined fa-2x mb-2 d-block opacity-50"></i>
                                        Belum ada data satuan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($units as $index => $unit): ?>
                                    <?php
                                    $searchText = strtolower(
                                        $unit['kode_satuan'] . ' ' .
                                        $unit['nama_satuan']
                                    );
                                    ?>
                                    <tr data-search="<?= esc($searchText, 'attr') ?>">
                                        <td class="text-center text-secondary"><?= $index + 1 ?></td>
                                        <td><span class="unit-code"><?= esc($unit['kode_satuan']) ?></span></td>
                                        <td class="fw-semibold"><?= esc($unit['nama_satuan']) ?></td>
                                        <td class="text-center">
                                            <?php if ((int) $unit['status'] === 1): ?>
                                                <span class="badge text-bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isAdmin): ?>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-edit-unit"
                                                            title="Edit Satuan"
                                                            data-id="<?= (int) $unit['id_satuan'] ?>"
                                                            data-kode="<?= esc($unit['kode_satuan'], 'attr') ?>"
                                                            data-nama="<?= esc($unit['nama_satuan'], 'attr') ?>"
                                                            data-status="<?= (int) $unit['status'] ?>">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form method="post" action="<?= base_url('pendataan/master/satuan/status/' . (int) $unit['id_satuan']) ?>"
                                                          onsubmit="return confirm('<?= (int) $unit['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?> satuan ini?')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit"
                                                                class="btn btn-sm <?= (int) $unit['status'] === 1 ? 'btn-outline-warning' : 'btn-outline-primary' ?>"
                                                                title="<?= (int) $unit['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                            <i class="fa-solid <?= (int) $unit['status'] === 1 ? 'fa-ban' : 'fa-check' ?>"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <tr id="unitNoResult" class="d-none">
                                <td colspan="<?= $isAdmin ? 5 : 4 ?>" class="text-center text-secondary py-4">
                                    Tidak ada satuan yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white small text-secondary">
                <i class="fa-solid fa-circle-info me-1"></i>
                Satuan yang sudah digunakan pada data barang tidak dihapus permanen. Gunakan status Nonaktif bila tidak digunakan lagi.
            </div>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content unit-modal shadow">
            <form id="unitForm" method="post" action="<?= base_url('pendataan/master/satuan/simpan') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_satuan" id="unitId" value="">

                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="unitModalTitle">
                        <span class="modal-title-icon"><i class="fa-solid fa-ruler-combined"></i></span>
                        <span id="unitModalTitleText">Tambah Satuan</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="unitCode" class="form-label">Kode Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="unitCode" name="kode_satuan"
                               maxlength="10" placeholder="Contoh: PCS" required>
                        <div class="form-text">Maksimal 10 karakter. Gunakan huruf, angka, titik, atau tanda hubung.</div>
                    </div>

                    <div class="mb-3">
                        <label for="unitName" class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="unitName" name="nama_satuan"
                               maxlength="30" placeholder="Contoh: Pieces" required>
                    </div>

                    <div class="mb-1">
                        <label for="unitStatus" class="form-label">Status</label>
                        <select class="form-select" id="unitStatus" name="status">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-success px-4" id="unitSubmitButton">
                        <i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($isAdmin): ?>
<script>
(function () {
    const modalElement = document.getElementById('unitModal');
    const form = document.getElementById('unitForm');
    const storeUrl = <?= json_encode(base_url('pendataan/master/satuan/simpan')) ?>;
    const updateUrl = <?= json_encode(base_url('pendataan/master/satuan/ubah')) ?>;
    const hasErrors = <?= json_encode(!empty($errors)) ?>;
    const errorEditId = <?= json_encode((int) $editUnitId) ?>;

    const title = document.getElementById('unitModalTitleText');
    const submit = document.getElementById('unitSubmitButton');
    const icon = document.querySelector('#unitModalTitle .modal-title-icon i');

    const oldValues = {
        id: <?= json_encode((string) old('id_satuan', $editUnitId ?? '')) ?>,
        kode: <?= json_encode((string) old('kode_satuan', '')) ?>,
        nama: <?= json_encode((string) old('nama_satuan', '')) ?>,
        status: <?= json_encode((string) old('status', '1')) ?>
    };

    function prepareAdd() {
        form.reset();
        form.action = storeUrl;
        document.getElementById('unitId').value = '';
        document.getElementById('unitStatus').value = '1';
        title.textContent = 'Tambah Satuan';
        submit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN';
        icon.className = 'fa-solid fa-ruler-combined';
    }

    function prepareEdit(data) {
        form.action = updateUrl + '/' + data.id;
        document.getElementById('unitId').value = data.id;
        document.getElementById('unitCode').value = data.kode;
        document.getElementById('unitName').value = data.nama;
        document.getElementById('unitStatus').value = String(data.status);
        title.textContent = 'Edit Satuan';
        submit.innerHTML = '<i class="fa-solid fa-check me-1"></i> SIMPAN PERUBAHAN';
        icon.className = 'fa-solid fa-pen';
    }

    document.getElementById('btnAddUnit')?.addEventListener('click', prepareAdd);

    document.querySelectorAll('.btn-edit-unit').forEach(button => {
        button.addEventListener('click', function () {
            prepareEdit({
                id: this.dataset.id,
                kode: this.dataset.kode,
                nama: this.dataset.nama,
                status: this.dataset.status
            });

            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    });

    document.getElementById('unitCode').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    if (hasErrors) {
        if (errorEditId || oldValues.id) {
            prepareEdit({
                id: errorEditId || oldValues.id,
                kode: oldValues.kode,
                nama: oldValues.nama,
                status: oldValues.status
            });
        } else {
            prepareAdd();
            document.getElementById('unitCode').value = oldValues.kode;
            document.getElementById('unitName').value = oldValues.nama;
            document.getElementById('unitStatus').value = oldValues.status;
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
})();
</script>
<?php endif; ?>

<script>
(function () {
    const search = document.getElementById('searchUnit');
    const rows = Array.from(document.querySelectorAll('#unitTableBody tr[data-search]'));
    const noResult = document.getElementById('unitNoResult');

    if (!search) return;

    search.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(row => {
            const matched = row.dataset.search.includes(keyword);
            row.classList.toggle('d-none', !matched);
            if (matched) visible++;
        });

        if (noResult) noResult.classList.toggle('d-none', keyword === '' || visible > 0);
    });
})();
</script>
<?= $this->endSection() ?>
