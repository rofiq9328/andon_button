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

    .factory-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        padding: .32rem .55rem;
        border-radius: 7px;
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
        font-weight: 700;
        font-size: .78rem;
    }

    .factory-modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
    }

    .factory-modal .modal-header {
        padding: 1.1rem 1.35rem;
        background: #fff;
    }

    .factory-modal .modal-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #212529;
    }

    .factory-modal .modal-title-icon {
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

    .factory-modal .modal-body {
        padding: 1.35rem;
    }

    .factory-modal .form-label {
        font-size: .88rem;
        font-weight: 600;
        color: #343a40;
    }

    .factory-modal .form-control:focus,
    .factory-modal .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .12);
    }

    .factory-modal .modal-footer {
        padding: .95rem 1.35rem;
        background: #fbfcfb;
    }

    @media (max-width: 575.98px) {
        .master-toolbar {
            width: 100%;
        }

        .master-search {
            max-width: none !important;
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors') ?? [];
$editFactoryId = session()->getFlashdata('edit_factory_id') ?? old('id_factory');
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-7">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Master Data</div>
                <h1 class="mb-1 fs-3">MASTER FACTORY</h1>
                <div class="master-intro">Kelola daftar Factory yang menjadi induk lokasi dan area pendataan barang.</div>
            </div>
            <div class="col-md-5 d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="<?= base_url('pendataan') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> DASHBOARD
                </a>
                <?php if (\App\Libraries\Roles::has('admin')): ?>
                    <button type="button" id="btnAddFactory" class="btn btn-success btn-sm fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#factoryModal">
                        <i class="fa-solid fa-plus me-1"></i> TAMBAH FACTORY
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
                    <h2 class="h6 fw-bold mb-0">Daftar Factory</h2>
                    <div class="small text-secondary mt-1"><?= count($factories) ?> data terdaftar</div>
                </div>
                <div class="input-group input-group-sm master-search" style="max-width: 280px;">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                    <input type="search" id="searchFactory" class="form-control" placeholder="Cari kode, nama, lokasi...">
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover master-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:65px;">No</th>
                                <th style="width:140px;">Kode Factory</th>
                                <th>Nama Factory</th>
                                <th>Lokasi</th>
                                <th class="text-center" style="width:120px;">Status</th>
                                <?php if (\App\Libraries\Roles::has('admin')): ?>
                                    <th class="text-end" style="width:130px;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="factoryTableBody">
                            <?php if (empty($factories)): ?>
                                <tr>
                                    <td colspan="<?= \App\Libraries\Roles::has('admin') ? 6 : 5 ?>"
                                        class="text-center text-secondary py-5">
                                        <i class="fa-solid fa-industry fa-2x mb-2 d-block opacity-50"></i>
                                        Belum ada data Factory. Silakan tambahkan Factory pertama.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($factories as $index => $factory): ?>
                                    <tr data-search="<?= esc(strtolower($factory['kode_factory'] . ' ' . $factory['nama_factory'] . ' ' . ($factory['lokasi'] ?? '')), 'attr') ?>">
                                        <td class="text-center text-secondary"><?= $index + 1 ?></td>
                                        <td><span class="factory-code"><?= esc($factory['kode_factory']) ?></span></td>
                                        <td class="fw-semibold"><?= esc($factory['nama_factory']) ?></td>
                                        <td><?= esc($factory['lokasi'] ?: '-') ?></td>
                                        <td class="text-center">
                                            <?php if ((int) $factory['status'] === 1): ?>
                                                <span class="badge text-bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (\App\Libraries\Roles::has('admin')): ?>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-edit-factory"
                                                            title="Edit Factory"
                                                            data-id="<?= (int) $factory['id_factory'] ?>"
                                                            data-kode="<?= esc($factory['kode_factory'], 'attr') ?>"
                                                            data-nama="<?= esc($factory['nama_factory'], 'attr') ?>"
                                                            data-lokasi="<?= esc($factory['lokasi'] ?? '', 'attr') ?>"
                                                            data-status="<?= (int) $factory['status'] ?>">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form method="post" action="<?= base_url('pendataan/master/factory/status/' . (int) $factory['id_factory']) ?>"
                                                          onsubmit="return confirm('<?= (int) $factory['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?> Factory ini?')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit"
                                                                class="btn btn-sm <?= (int) $factory['status'] === 1 ? 'btn-outline-warning' : 'btn-outline-primary' ?>"
                                                                title="<?= (int) $factory['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                            <i class="fa-solid <?= (int) $factory['status'] === 1 ? 'fa-ban' : 'fa-check' ?>"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr id="factoryNoResult" class="d-none">
                                <td colspan="<?= \App\Libraries\Roles::has('admin') ? 6 : 5 ?>"
                                    class="text-center text-secondary py-4">
                                    Tidak ada Factory yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white small text-secondary">
                <i class="fa-solid fa-circle-info me-1"></i>
                Factory yang sudah digunakan tidak dihapus; gunakan status Nonaktif bila sudah tidak digunakan.
            </div>
        </div>
    </div>
</div>

<?php if (\App\Libraries\Roles::has('admin')): ?>
<div class="modal fade" id="factoryModal" tabindex="-1" aria-labelledby="factoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content factory-modal shadow">
            <form id="factoryForm" method="post" action="<?= base_url('pendataan/master/factory/simpan') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_factory" id="factoryId" value="">

                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="factoryModalTitle">
                        <span class="modal-title-icon"><i class="fa-solid fa-industry"></i></span>
                        <span id="factoryModalTitleText">Tambah Factory</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="factoryCode" class="form-label">Kode Factory <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="factoryCode" name="kode_factory"
                               maxlength="10" placeholder="Contoh: A atau B" required>
                        <div class="form-text">Maksimal 10 karakter. Gunakan huruf, angka, titik, atau tanda hubung.</div>
                    </div>

                    <div class="mb-3">
                        <label for="factoryName" class="form-label">Nama Factory <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="factoryName" name="nama_factory"
                               maxlength="100" placeholder="Masukkan nama Factory" required>
                    </div>

                    <div class="mb-3">
                        <label for="factoryLocation" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="factoryLocation" name="lokasi"
                               maxlength="255" placeholder="Masukkan lokasi (opsional)">
                    </div>

                    <div class="mb-1">
                        <label for="factoryStatus" class="form-label">Status</label>
                        <select class="form-select" id="factoryStatus" name="status">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-success px-4" id="factorySubmitButton">
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
<?php if (\App\Libraries\Roles::has('admin')): ?>
<script>
(function () {
    const modalElement = document.getElementById('factoryModal');
    const form = document.getElementById('factoryForm');
    const storeUrl = <?= json_encode(base_url('pendataan/master/factory/simpan')) ?>;
    const updateUrl = <?= json_encode(base_url('pendataan/master/factory/ubah')) ?>;
    const hasErrors = <?= json_encode(!empty($errors)) ?>;
    const errorEditId = <?= json_encode((int) $editFactoryId) ?>;
    const oldValues = {
        id: <?= json_encode((string) old('id_factory', $editFactoryId ?? '')) ?>,
        kode: <?= json_encode((string) old('kode_factory', '')) ?>,
        nama: <?= json_encode((string) old('nama_factory', '')) ?>,
        lokasi: <?= json_encode((string) old('lokasi', '')) ?>,
        status: <?= json_encode((string) old('status', '1')) ?>
    };

    const title = document.getElementById('factoryModalTitleText');
    const submit = document.getElementById('factorySubmitButton');
    const icon = document.querySelector('#factoryModalTitle .modal-title-icon i');

    function prepareAdd() {
        form.reset();
        form.action = storeUrl;
        document.getElementById('factoryId').value = '';
        document.getElementById('factoryStatus').value = '1';
        title.textContent = 'Tambah Factory';
        submit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN';
        icon.className = 'fa-solid fa-industry';
    }

    function prepareEdit(data) {
        form.action = updateUrl + '/' + data.id;
        document.getElementById('factoryId').value = data.id;
        document.getElementById('factoryCode').value = data.kode;
        document.getElementById('factoryName').value = data.nama;
        document.getElementById('factoryLocation').value = data.lokasi || '';
        document.getElementById('factoryStatus').value = String(data.status);
        title.textContent = 'Edit Factory';
        submit.innerHTML = '<i class="fa-solid fa-check me-1"></i> SIMPAN PERUBAHAN';
        icon.className = 'fa-solid fa-pen';
    }

    document.getElementById('btnAddFactory')?.addEventListener('click', prepareAdd);

    document.querySelectorAll('.btn-edit-factory').forEach(button => {
        button.addEventListener('click', function () {
            prepareEdit({
                id: this.dataset.id,
                kode: this.dataset.kode,
                nama: this.dataset.nama,
                lokasi: this.dataset.lokasi,
                status: this.dataset.status
            });
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    });

    document.getElementById('factoryCode').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    if (hasErrors) {
        const editId = errorEditId || oldValues.id;
        if (editId) {
            prepareEdit({
                id: editId,
                kode: oldValues.kode,
                nama: oldValues.nama,
                lokasi: oldValues.lokasi,
                status: oldValues.status
            });
        } else {
            prepareAdd();
            document.getElementById('factoryCode').value = oldValues.kode;
            document.getElementById('factoryName').value = oldValues.nama;
            document.getElementById('factoryLocation').value = oldValues.lokasi;
            document.getElementById('factoryStatus').value = oldValues.status;
        }
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
})();
</script>
<?php endif; ?>

<script>
(function () {
    const search = document.getElementById('searchFactory');
    const rows = Array.from(document.querySelectorAll('#factoryTableBody tr[data-search]'));
    const noResult = document.getElementById('factoryNoResult');

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
