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

    .category-code {
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

    .category-modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
    }

    .category-modal .modal-header {
        padding: 1.1rem 1.35rem;
        background: #fff;
    }

    .category-modal .modal-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #212529;
    }

    .category-modal .modal-title-icon {
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

    .category-modal .modal-body {
        padding: 1.35rem;
    }

    .category-modal .form-label {
        font-size: .88rem;
        font-weight: 600;
        color: #343a40;
    }

    .category-modal .form-control:focus,
    .category-modal .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .12);
    }

    .category-modal .modal-footer {
        padding: .95rem 1.35rem;
        background: #fbfcfb;
    }

    @media (max-width: 575.98px) {
        .category-search {
            max-width: none !important;
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors') ?? [];
$editCategoryId = session()->getFlashdata('edit_kategori_id') ?? old('id_kategori');
$isAdmin = \App\Libraries\Roles::has('admin');
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-7">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Master Data</div>
                <h1 class="mb-1 fs-3">MASTER KATEGORI BARANG</h1>
                <div class="master-intro">Kelola kelompok atau jenis barang untuk memudahkan pencatatan dan pelaporan.</div>
            </div>
            <div class="col-md-5 d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="<?= base_url('pendataan') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> DASHBOARD
                </a>
                <?php if ($isAdmin): ?>
                    <button type="button" id="btnAddCategory" class="btn btn-success btn-sm fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="fa-solid fa-plus me-1"></i> TAMBAH KATEGORI
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
                    <h2 class="h6 fw-bold mb-0">Daftar Kategori Barang</h2>
                    <div class="small text-secondary mt-1"><?= count($categories) ?> kategori terdaftar</div>
                </div>
                <div class="input-group input-group-sm category-search" style="max-width: 280px;">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                    <input type="search" id="searchCategory" class="form-control" placeholder="Cari kode atau kategori...">
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover master-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:65px;">No</th>
                                <th style="width:160px;">Kode Kategori</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-center" style="width:115px;">Status</th>
                                <?php if ($isAdmin): ?>
                                    <th class="text-end" style="width:125px;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="categoryTableBody">
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? 6 : 5 ?>" class="text-center text-secondary py-5">
                                        <i class="fa-solid fa-tags fa-2x mb-2 d-block opacity-50"></i>
                                        Belum ada kategori barang.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $index => $category): ?>
                                    <?php
                                    $searchText = strtolower(
                                        $category['kode_kategori'] . ' ' .
                                        $category['nama_kategori'] . ' ' .
                                        ($category['deskripsi'] ?? '')
                                    );
                                    ?>
                                    <tr data-search="<?= esc($searchText, 'attr') ?>">
                                        <td class="text-center text-secondary"><?= $index + 1 ?></td>
                                        <td><span class="category-code"><?= esc($category['kode_kategori']) ?></span></td>
                                        <td class="fw-semibold"><?= esc($category['nama_kategori']) ?></td>
                                        <td class="text-secondary"><?= esc($category['deskripsi'] ?: '-') ?></td>
                                        <td class="text-center">
                                            <?php if ((int) $category['status'] === 1): ?>
                                                <span class="badge text-bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isAdmin): ?>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-edit-category"
                                                            title="Edit Kategori"
                                                            data-id="<?= (int) $category['id_kategori'] ?>"
                                                            data-kode="<?= esc($category['kode_kategori'], 'attr') ?>"
                                                            data-nama="<?= esc($category['nama_kategori'], 'attr') ?>"
                                                            data-deskripsi="<?= esc($category['deskripsi'] ?? '', 'attr') ?>"
                                                            data-status="<?= (int) $category['status'] ?>">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form method="post" action="<?= base_url('pendataan/master/kategori/status/' . (int) $category['id_kategori']) ?>"
                                                          onsubmit="return confirm('<?= (int) $category['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?> kategori ini?')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit"
                                                                class="btn btn-sm <?= (int) $category['status'] === 1 ? 'btn-outline-warning' : 'btn-outline-primary' ?>"
                                                                title="<?= (int) $category['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                            <i class="fa-solid <?= (int) $category['status'] === 1 ? 'fa-ban' : 'fa-check' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <form method="post" action="<?= base_url('pendataan/master/kategori/hapus/' . (int) $category['id_kategori']) ?>"
                                                          onsubmit="return confirm('Hapus kategori ini secara permanen? Tindakan ini tidak dapat dibatalkan.');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit"
                                                                class="btn btn-sm <?= (int) $category['jumlah_barang'] > 0 ? 'btn-outline-secondary' : 'btn-outline-danger' ?>"
                                                                title="<?= (int) $category['jumlah_barang'] > 0 ? 'Tidak dapat dihapus: dipakai ' . (int) $category['jumlah_barang'] . ' barang' : 'Hapus kategori' ?>"
                                                                <?= (int) $category['jumlah_barang'] > 0 ? 'disabled' : '' ?>>
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <tr id="categoryNoResult" class="d-none">
                                <td colspan="<?= $isAdmin ? 6 : 5 ?>" class="text-center text-secondary py-4">
                                    Tidak ada kategori yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white small text-secondary">
                <i class="fa-solid fa-circle-info me-1"></i>
                Kategori hanya dapat dihapus jika belum digunakan oleh data barang. Jika masih digunakan, ubah kategori pada data barang atau nonaktifkan kategori.
            </div>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content category-modal shadow">
            <form id="categoryForm" method="post" action="<?= base_url('pendataan/master/kategori/simpan') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kategori" id="categoryId" value="">

                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="categoryModalTitle">
                        <span class="modal-title-icon"><i class="fa-solid fa-tags"></i></span>
                        <span id="categoryModalTitleText">Tambah Kategori</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryCode" class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="categoryCode" name="kode_kategori"
                               maxlength="20" placeholder="Contoh: KAT-IT" required>
                        <div class="form-text">Maksimal 20 karakter. Gunakan huruf, angka, titik, atau tanda hubung.</div>
                    </div>

                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" name="nama_kategori"
                               maxlength="100" placeholder="Masukkan nama kategori" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="categoryDescription" name="deskripsi" rows="3"
                                  placeholder="Keterangan kategori (opsional)"></textarea>
                    </div>

                    <div class="mb-1">
                        <label for="categoryStatus" class="form-label">Status</label>
                        <select class="form-select" id="categoryStatus" name="status">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-success px-4" id="categorySubmitButton">
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
    const modalElement = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    const storeUrl = <?= json_encode(base_url('pendataan/master/kategori/simpan')) ?>;
    const updateUrl = <?= json_encode(base_url('pendataan/master/kategori/ubah')) ?>;
    const hasErrors = <?= json_encode(!empty($errors)) ?>;
    const errorEditId = <?= json_encode((int) $editCategoryId) ?>;

    const title = document.getElementById('categoryModalTitleText');
    const submit = document.getElementById('categorySubmitButton');
    const icon = document.querySelector('#categoryModalTitle .modal-title-icon i');

    const oldValues = {
        id: <?= json_encode((string) old('id_kategori', $editCategoryId ?? '')) ?>,
        kode: <?= json_encode((string) old('kode_kategori', '')) ?>,
        nama: <?= json_encode((string) old('nama_kategori', '')) ?>,
        deskripsi: <?= json_encode((string) old('deskripsi', '')) ?>,
        status: <?= json_encode((string) old('status', '1')) ?>
    };

    function prepareAdd() {
        form.reset();
        form.action = storeUrl;
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryStatus').value = '1';
        title.textContent = 'Tambah Kategori';
        submit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN';
        icon.className = 'fa-solid fa-tags';
    }

    function prepareEdit(data) {
        form.action = updateUrl + '/' + data.id;
        document.getElementById('categoryId').value = data.id;
        document.getElementById('categoryCode').value = data.kode;
        document.getElementById('categoryName').value = data.nama;
        document.getElementById('categoryDescription').value = data.deskripsi || '';
        document.getElementById('categoryStatus').value = String(data.status);
        title.textContent = 'Edit Kategori';
        submit.innerHTML = '<i class="fa-solid fa-check me-1"></i> SIMPAN PERUBAHAN';
        icon.className = 'fa-solid fa-pen';
    }

    document.getElementById('btnAddCategory')?.addEventListener('click', prepareAdd);

    document.querySelectorAll('.btn-edit-category').forEach(button => {
        button.addEventListener('click', function () {
            prepareEdit({
                id: this.dataset.id,
                kode: this.dataset.kode,
                nama: this.dataset.nama,
                deskripsi: this.dataset.deskripsi,
                status: this.dataset.status
            });

            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    });

    document.getElementById('categoryCode').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    if (hasErrors) {
        if (errorEditId || oldValues.id) {
            prepareEdit({
                id: errorEditId || oldValues.id,
                kode: oldValues.kode,
                nama: oldValues.nama,
                deskripsi: oldValues.deskripsi,
                status: oldValues.status
            });
        } else {
            prepareAdd();
            document.getElementById('categoryCode').value = oldValues.kode;
            document.getElementById('categoryName').value = oldValues.nama;
            document.getElementById('categoryDescription').value = oldValues.deskripsi;
            document.getElementById('categoryStatus').value = oldValues.status;
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
})();
</script>
<?php endif; ?>

<script>
(function () {
    const search = document.getElementById('searchCategory');
    const rows = Array.from(document.querySelectorAll('#categoryTableBody tr[data-search]'));
    const noResult = document.getElementById('categoryNoResult');

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
