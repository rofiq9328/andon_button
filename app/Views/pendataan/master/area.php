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
        font-size: .84rem;
    }

    .area-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 56px;
        padding: .32rem .55rem;
        border-radius: 7px;
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
        font-weight: 700;
        font-size: .78rem;
    }

    .scope-badge {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .area-modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
    }

    .area-modal .modal-header {
        padding: 1.1rem 1.35rem;
        background: #fff;
    }

    .area-modal .modal-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #212529;
    }

    .area-modal .modal-title-icon {
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

    .area-modal .modal-body {
        padding: 1.35rem;
    }

    .area-modal .form-label {
        font-size: .88rem;
        font-weight: 600;
        color: #343a40;
    }

    .area-modal .form-control:focus,
    .area-modal .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .12);
    }

    .area-modal .modal-footer {
        padding: .95rem 1.35rem;
        background: #fbfcfb;
    }

    .scope-info {
        padding: .7rem .85rem;
        border-radius: 8px;
        background: var(--bs-info-bg-subtle);
        color: var(--bs-info-text-emphasis);
        font-size: .77rem;
    }

    @media (max-width: 575.98px) {
        .area-search {
            max-width: none !important;
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors') ?? [];
$editAreaId = session()->getFlashdata('edit_area_id') ?? old('id_area');
$isAdmin = \App\Libraries\Roles::has('admin');
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-7">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Master Data</div>
                <h1 class="mb-1 fs-3">MASTER AREA</h1>
                <div class="master-intro">Kelola lokasi Pusat dan Factory, termasuk pembagian area dan induknya.</div>
            </div>
            <div class="col-md-5 d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="<?= base_url('pendataan') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> DASHBOARD
                </a>
                <?php if ($isAdmin): ?>
                    <button type="button" id="btnAddArea" class="btn btn-success btn-sm fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#areaModal">
                        <i class="fa-solid fa-plus me-1"></i> TAMBAH AREA
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
                    <h2 class="h6 fw-bold mb-0">Daftar Area</h2>
                    <div class="small text-secondary mt-1"><?= count($areas) ?> data terdaftar</div>
                </div>
                <div class="input-group input-group-sm area-search" style="max-width: 300px;">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                    <input type="search" id="searchArea" class="form-control" placeholder="Cari kode, nama, Factory...">
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover master-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:60px;">No</th>
                                <th style="width:125px;">Kode Area</th>
                                <th>Nama Area</th>
                                <th style="width:125px;">Cakupan</th>
                                <th>Factory</th>
                                <th>Induk Area</th>
                                <th style="width:145px;">Jenis</th>
                                <th style="width:165px;">Fungsi Stok</th>
                                <th class="text-center" style="width:105px;">Status</th>
                                <?php if ($isAdmin): ?>
                                    <th class="text-end" style="width:125px;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="areaTableBody">
                            <?php if (empty($areas)): ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? 10 : 9 ?>" class="text-center text-secondary py-5">
                                        <i class="fa-solid fa-map-location-dot fa-2x mb-2 d-block opacity-50"></i>
                                        Belum ada data Area. Silakan tambahkan Area pertama.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($areas as $index => $area): ?>
                                    <?php
                                    $factoryLabel = $area['kode_factory']
                                        ? $area['kode_factory'] . ' - ' . $area['nama_factory']
                                        : '-';
                                    $parentLabel = $area['parent_kode']
                                        ? $area['parent_kode'] . ' - ' . $area['parent_nama']
                                        : '-';
                                    $searchText = strtolower(
                                        $area['kode_area'] . ' ' .
                                        $area['nama_area'] . ' ' .
                                        $factoryLabel . ' ' .
                                        $parentLabel . ' ' .
                                        $area['jenis_area'] . ' ' .
                                        $area['fungsi_stok'] . ' ' .
                                        $area['cakupan_area']
                                    );
                                    ?>
                                    <tr data-search="<?= esc($searchText, 'attr') ?>">
                                        <td class="text-center text-secondary"><?= $index + 1 ?></td>
                                        <td><span class="area-code"><?= esc($area['kode_area']) ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= esc($area['nama_area']) ?></div>
                                            <?php if (!empty($area['deskripsi'])): ?>
                                                <div class="small text-secondary"><?= esc($area['deskripsi']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($area['cakupan_area'] === 'PUSAT'): ?>
                                                <span class="badge text-bg-primary scope-badge">PUSAT</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-info scope-badge">FACTORY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($factoryLabel) ?></td>
                                        <td><?= esc($parentLabel) ?></td>
                                        <td>
                                            <span class="badge text-bg-light border text-dark">
                                                <?= esc(str_replace('_', ' ', $area['jenis_area'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $fungsiStokLabel = [
                                                'PENGGUNA' => 'Area Pengguna',
                                                'PENYIMPANAN' => 'Lokasi Penyimpanan',
                                                'KEDUANYA' => 'Keduanya',
                                            ];
                                            $fungsiStokClass = [
                                                'PENGGUNA' => 'text-bg-primary',
                                                'PENYIMPANAN' => 'text-bg-info',
                                                'KEDUANYA' => 'text-bg-success',
                                            ];
                                            ?>
                                            <span class="badge <?= esc($fungsiStokClass[$area['fungsi_stok']] ?? 'text-bg-secondary') ?>">
                                                <?= esc($fungsiStokLabel[$area['fungsi_stok']] ?? $area['fungsi_stok']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int) $area['status'] === 1): ?>
                                                <span class="badge text-bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isAdmin): ?>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-edit-area"
                                                            title="Edit Area"
                                                            data-id="<?= (int) $area['id_area'] ?>"
                                                            data-factory="<?= esc((string) ($area['id_factory'] ?? ''), 'attr') ?>"
                                                            data-kode="<?= esc($area['kode_area'], 'attr') ?>"
                                                            data-nama="<?= esc($area['nama_area'], 'attr') ?>"
                                                            data-jenis="<?= esc($area['jenis_area'], 'attr') ?>"
                                                            data-fungsi="<?= esc($area['fungsi_stok'] ?? 'PENGGUNA', 'attr') ?>"
                                                            data-deskripsi="<?= esc($area['deskripsi'] ?? '', 'attr') ?>"
                                                            data-status="<?= (int) $area['status'] ?>">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form method="post" action="<?= base_url('pendataan/master/area/status/' . (int) $area['id_area']) ?>"
                                                          onsubmit="return confirm('<?= (int) $area['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?> Area ini?')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit"
                                                                class="btn btn-sm <?= (int) $area['status'] === 1 ? 'btn-outline-warning' : 'btn-outline-primary' ?>"
                                                                title="<?= (int) $area['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                            <i class="fa-solid <?= (int) $area['status'] === 1 ? 'fa-ban' : 'fa-check' ?>"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <tr id="areaNoResult" class="d-none">
                                <td colspan="<?= $isAdmin ? 10 : 9 ?>" class="text-center text-secondary py-4">
                                    Tidak ada Area yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white small text-secondary">
                <i class="fa-solid fa-circle-info me-1"></i>
                Data Area tidak dihapus permanen. Gunakan status Nonaktif bila lokasi sudah tidak digunakan.
            </div>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 700px;">
        <div class="modal-content area-modal shadow">
            <form id="areaForm" method="post" action="<?= base_url('pendataan/master/area/simpan') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_area" id="areaId" value="">

                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="areaModalTitle">
                        <span class="modal-title-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                        <span id="areaModalTitleText">Tambah Area</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6" id="factoryField">
                            <label for="areaFactory" class="form-label">Factory</label>
                            <select class="form-select" id="areaFactory" name="id_factory">
                                <option value="">Tanpa Factory (Area Pusat)</option>
                                <?php foreach ($factories as $factory): ?>
                                    <option value="<?= (int) $factory['id_factory'] ?>">
                                        <?= esc($factory['kode_factory'] . ' - ' . $factory['nama_factory']) ?>
                                        <?= (int) $factory['status'] === 1 ? '' : ' (Nonaktif)' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Pilih Factory untuk Area di lingkungan Factory. Kosongkan untuk Area Pusat.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="areaType" class="form-label">Jenis Area <span class="text-danger">*</span></label>
                            <select class="form-select" id="areaType" name="jenis_area" required>
                                <option value="LINE">Line</option>
                                <option value="OFFICE">Office</option>
                                <option value="WAREHOUSE">Warehouse</option>
                                <option value="MAINTENANCE">Maintenance</option>
                                <option value="DEPARTMENT">Department</option>
                                <option value="OTHER">Lainnya</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="areaStockFunction" class="form-label">Fungsi Stok <span class="text-danger">*</span></label>
                            <select class="form-select" id="areaStockFunction" name="fungsi_stok" required>
                                <option value="PENGGUNA">Area Pengguna</option>
                                <option value="PENYIMPANAN">Lokasi Penyimpanan</option>
                                <option value="KEDUANYA">Keduanya</option>
                            </select>
                            <div class="form-text">Tidak bergantung pada Jenis Area. Office, Warehouse, dan Area lainnya dapat menjadi lokasi penyimpanan.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="areaCode" class="form-label">Kode Area <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="areaCode" name="kode_area"
                                   maxlength="30" placeholder="Contoh: A-LINE-01" required>
                            <div class="form-text">Maksimal 30 karakter; gunakan huruf, angka, titik, atau tanda hubung.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="areaName" class="form-label">Nama Area <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="areaName" name="nama_area"
                                   maxlength="100" placeholder="Masukkan nama Area" required>
                        </div>

                        <div class="col-12">
                            <label for="areaDescription" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="areaDescription" name="deskripsi" rows="2"
                                      placeholder="Keterangan tambahan (opsional)"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="areaStatus" class="form-label">Status</label>
                            <select class="form-select" id="areaStatus" name="status">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="scope-info">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Cakupan Area ditentukan otomatis: pilih Factory untuk Area Factory, atau kosongkan untuk Area Pusat.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-success px-4" id="areaSubmitButton">
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
    const modalElement = document.getElementById('areaModal');
    const form = document.getElementById('areaForm');
    const storeUrl = <?= json_encode(base_url('pendataan/master/area/simpan')) ?>;
    const updateUrl = <?= json_encode(base_url('pendataan/master/area/ubah')) ?>;
    const hasErrors = <?= json_encode(!empty($errors)) ?>;
    const errorEditId = <?= json_encode((int) $editAreaId) ?>;

    const factorySelect = document.getElementById('areaFactory');
    const title = document.getElementById('areaModalTitleText');
    const submit = document.getElementById('areaSubmitButton');
    const icon = document.querySelector('#areaModalTitle .modal-title-icon i');

    const oldValues = {
        id: <?= json_encode((string) old('id_area', $editAreaId ?? '')) ?>,
        factory: <?= json_encode((string) old('id_factory', '')) ?>,
        kode: <?= json_encode((string) old('kode_area', '')) ?>,
        nama: <?= json_encode((string) old('nama_area', '')) ?>,
        jenis: <?= json_encode((string) old('jenis_area', 'OTHER')) ?>,
        fungsi: <?= json_encode((string) old('fungsi_stok', 'PENGGUNA')) ?>,
        deskripsi: <?= json_encode((string) old('deskripsi', '')) ?>,
        status: <?= json_encode((string) old('status', '1')) ?>
    };

    function prepareAdd() {
        form.reset();
        form.action = storeUrl;
        document.getElementById('areaId').value = '';
        factorySelect.value = '';
        document.getElementById('areaType').value = 'OTHER';
        document.getElementById('areaStockFunction').value = 'PENGGUNA';
        document.getElementById('areaStatus').value = '1';
        document.getElementById('areaDescription').value = '';
        title.textContent = 'Tambah Area';
        submit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN';
        icon.className = 'fa-solid fa-map-location-dot';
    }

    function prepareEdit(data) {
        form.action = updateUrl + '/' + data.id;
        document.getElementById('areaId').value = data.id;
        factorySelect.value = data.factory || '';
        document.getElementById('areaCode').value = data.kode || '';
        document.getElementById('areaName').value = data.nama || '';
        document.getElementById('areaType').value = data.jenis || 'OTHER';
        document.getElementById('areaStockFunction').value = data.fungsi || 'PENGGUNA';
        document.getElementById('areaDescription').value = data.deskripsi || '';
        document.getElementById('areaStatus').value = String(data.status);
        title.textContent = 'Edit Area';
        submit.innerHTML = '<i class="fa-solid fa-check me-1"></i> SIMPAN PERUBAHAN';
        icon.className = 'fa-solid fa-pen';
    }

    document.getElementById('btnAddArea')?.addEventListener('click', prepareAdd);

    document.querySelectorAll('.btn-edit-area').forEach(button => {
        button.addEventListener('click', function () {
            prepareEdit({
                id: this.dataset.id,
                factory: this.dataset.factory,
                kode: this.dataset.kode,
                nama: this.dataset.nama,
                jenis: this.dataset.jenis,
                fungsi: this.dataset.fungsi,
                deskripsi: this.dataset.deskripsi,
                status: this.dataset.status
            });

            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    });

    document.getElementById('areaCode').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    if (hasErrors) {
        if (errorEditId || oldValues.id) {
            prepareEdit({
                id: errorEditId || oldValues.id,
                factory: oldValues.factory,
                kode: oldValues.kode,
                nama: oldValues.nama,
                jenis: oldValues.jenis,
                fungsi: oldValues.fungsi,
                deskripsi: oldValues.deskripsi,
                status: oldValues.status
            });
        } else {
            prepareAdd();
            factorySelect.value = oldValues.factory;
            document.getElementById('areaCode').value = oldValues.kode;
            document.getElementById('areaName').value = oldValues.nama;
            document.getElementById('areaType').value = oldValues.jenis;
            document.getElementById('areaStockFunction').value = oldValues.fungsi;
            document.getElementById('areaDescription').value = oldValues.deskripsi;
            document.getElementById('areaStatus').value = oldValues.status;
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
})();
</script>
<?php endif; ?>

<script>
(function () {
    const search = document.getElementById('searchArea');
    const rows = Array.from(document.querySelectorAll('#areaTableBody tr[data-search]'));
    const noResult = document.getElementById('areaNoResult');

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
