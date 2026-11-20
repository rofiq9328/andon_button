<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .master-intro{color:var(--bs-secondary-color);font-size:.88rem}
    .master-card{border:1px solid var(--bs-border-color);border-top:3px solid var(--bs-success);border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
    .master-card .card-header{background:var(--bs-body-bg);border-bottom:1px solid var(--bs-border-color);padding:.85rem 1rem}
    .master-table th{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
    .master-table td{vertical-align:middle;font-size:.84rem}
    .standard-modal .modal-content{border:0;border-radius:14px;overflow:hidden}
    .standard-modal .modal-header{padding:1.1rem 1.35rem;background:#fff}
    .standard-modal .modal-title{display:flex;align-items:center;gap:.75rem;font-size:1.05rem;font-weight:700;color:#212529}
    .standard-modal .modal-title-icon{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;background:#e8f5ee;color:#198754;font-size:1.1rem;flex-shrink:0}
    .standard-modal .modal-body{padding:1.35rem}
    .standard-modal .form-label{font-size:.88rem;font-weight:600;color:#343a40}
    .standard-modal .form-control:focus,.standard-modal .form-select:focus{border-color:#198754;box-shadow:0 0 0 .2rem rgba(25,135,84,.12)}
    .standard-modal .modal-footer{padding:.95rem 1.35rem;background:#fbfcfb}
    .standard-stat{height:100%;border:1px solid var(--bs-border-color);border-radius:9px;padding:1rem;background:var(--bs-body-bg)}
    .standard-stat-label{font-size:.72rem;color:var(--bs-secondary-color);font-weight:700;text-transform:uppercase;letter-spacing:.04em}
    .standard-stat-value{font-size:1.45rem;font-weight:800;line-height:1.2;margin-top:.3rem}
    .standard-code{display:inline-flex;padding:.3rem .5rem;border-radius:7px;background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis);font-size:.77rem;font-weight:700}
    .standard-note{padding:.75rem .9rem;border-radius:8px;background:var(--bs-info-bg-subtle);color:var(--bs-info-text-emphasis);font-size:.8rem}
    .standard-filter .form-label{font-size:.78rem;font-weight:650;color:var(--bs-secondary-color);margin-bottom:.3rem}
    @media(max-width:575.98px){.standard-search{max-width:none!important;width:100%}}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$errors = session()->getFlashdata('errors') ?? [];
$editId = session()->getFlashdata('edit_standar_id') ?? old('id_standar');
$isAdmin = \App\Libraries\Roles::has('admin');
$today = date('Y-m-d');
$periodLabel = function ($row) use ($today) {
    if ((int) $row['status'] !== 1) return ['Nonaktif', 'text-bg-secondary'];
    if ($row['berlaku_mulai'] > $today) return ['Akan Datang', 'text-bg-info'];
    if (!empty($row['berlaku_sampai']) && $row['berlaku_sampai'] < $today) return ['Berakhir', 'text-bg-warning'];
    return ['Berlaku', 'text-bg-success'];
};
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-7">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Master Data</div>
                <h1 class="mb-1 fs-3">KEBUTUHAN AREA</h1>
                <div class="master-intro">Atur barang dan jumlah standar untuk masing-masing Area. Setiap Area boleh memiliki daftar kebutuhan yang berbeda atau tanpa standar.</div>
            </div>
            <div class="col-md-5 d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="<?= base_url('pendataan/master/area') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-map-location-dot me-1"></i> MASTER AREA
                </a>
                <?php if ($isAdmin): ?>
                    <button type="button" id="btnAddStandard" class="btn btn-success btn-sm fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#standardModal" <?= empty($areas) || empty($barang) ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-plus me-1"></i> TAMBAH KEBUTUHAN
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
                <ul class="mb-0 ps-3"><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4">
                <div class="standard-stat">
                    <div class="standard-stat-label">Standar Aktif</div>
                    <div class="standard-stat-value"><?= number_format($activeCount) ?></div>
                    <div class="small text-secondary mt-1">Konfigurasi standar yang diaktifkan</div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="standard-stat">
                    <div class="standard-stat-label">Sedang Berlaku</div>
                    <div class="standard-stat-value text-success"><?= number_format($currentCount) ?></div>
                    <div class="small text-secondary mt-1">Sesuai tanggal hari ini</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="standard-stat">
                    <div class="standard-stat-label">Pengaturan Fleksibel</div>
                    <div class="fw-bold mt-2">Standar per Area dan Barang</div>
                    <div class="small text-secondary mt-1">Area penyimpanan boleh tidak memiliki standar kebutuhan.</div>
                </div>
            </div>
        </div>

        <?php if (empty($areas) || empty($barang)): ?>
            <div class="alert alert-warning shadow-sm">
                Kebutuhan Area memerlukan minimal satu Area aktif dan satu Master Barang aktif.
                <?php if (empty($areas)): ?><a href="<?= base_url('pendataan/master/area') ?>" class="alert-link">Buka Master Area</a>.<?php endif; ?>
                <?php if (empty($barang)): ?><a href="<?= base_url('pendataan/master/barang') ?>" class="alert-link">Buka Master Barang</a>.<?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="standard-note mb-3">
            <i class="fa-solid fa-circle-info me-1"></i>
            Standar berlaku berdasarkan periode tanggal. Untuk mengganti standar pada periode berikutnya, tutup tanggal berlaku standar lama terlebih dahulu, lalu tambahkan periode baru. Periode standar aktif yang bertumpuk akan ditolak.
        </div>

        <div class="card master-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="h6 fw-bold mb-0">Daftar Kebutuhan Barang per Area</h2>
                    <div class="small text-secondary mt-1"><?= count($items) ?> data ditampilkan</div>
                </div>
                <div class="input-group input-group-sm standard-search" style="max-width:300px">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                    <input type="search" id="searchStandard" class="form-control" placeholder="Cari area atau barang...">
                </div>
            </div>

            <div class="card-body border-bottom">
                <form method="get" action="<?= base_url('pendataan/master/kebutuhan-area') ?>" class="row g-2 align-items-end standard-filter">
                    <div class="col-12 col-md-5">
                        <label class="form-label">Area</label>
                        <select name="area" class="form-select form-select-sm">
                            <option value="">Semua Area</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>" <?= $filter['area'] === (string) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= esc($area['kode_area'] . ' - ' . $area['nama_area']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Status Data</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="AKTIF" <?= $filter['status'] === 'AKTIF' ? 'selected' : '' ?>>Aktif</option>
                            <option value="NONAKTIF" <?= $filter['status'] === 'NONAKTIF' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Pencarian</label>
                        <input type="search" name="q" class="form-control form-control-sm" value="<?= esc($filter['q']) ?>" placeholder="Kode/nama barang...">
                    </div>
                    <div class="col-12 col-md-1 d-flex gap-2">
                        <button class="btn btn-success btn-sm flex-grow-1" title="Terapkan filter"><i class="fa-solid fa-search"></i></button>
                        <a href="<?= base_url('pendataan/master/kebutuhan-area') ?>" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover master-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:55px">No</th>
                                <th>Area</th>
                                <th>Barang</th>
                                <th class="text-end">Jumlah Standar</th>
                                <th>Periode Berlaku</th>
                                <th>Status Periode</th>
                                <?php if ($isAdmin): ?><th class="text-end">Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="standardTableBody">
                        <?php if (empty($items)): ?>
                            <tr><td colspan="<?= $isAdmin ? 7 : 6 ?>" class="text-center text-secondary py-5">
                                <i class="fa-solid fa-clipboard-list fa-2x mb-2 d-block opacity-50"></i>
                                Belum ada kebutuhan barang yang diatur untuk Area ini.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $i => $item): ?>
                                <?php
                                [$statusText, $statusBadge] = $periodLabel($item);
                                $areaText = $item['kode_area'] . ' ' . $item['nama_area'];
                                $barangText = $item['kode_barang'] . ' ' . $item['nama_barang'];
                                $searchText = strtolower($areaText . ' ' . $barangText . ' ' . ($item['keterangan'] ?? ''));
                                ?>
                                <tr data-search="<?= esc($searchText, 'attr') ?>">
                                    <td class="text-center text-secondary"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($item['nama_area']) ?></div>
                                        <span class="standard-code"><?= esc($item['kode_area']) ?></span>
                                        <?php if (($item['fungsi_stok'] ?? '') === 'PENYIMPANAN'): ?>
                                            <span class="badge text-bg-info">Penyimpanan</span>
                                        <?php elseif (($item['fungsi_stok'] ?? '') === 'KEDUANYA'): ?>
                                            <span class="badge text-bg-success">Keduanya</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($item['nama_barang']) ?></div>
                                        <span class="standard-code"><?= esc($item['kode_barang']) ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-nowrap">
                                        <?= esc(number_format((float) $item['jumlah_standar'], 2, ',', '.')) ?>
                                        <small class="text-secondary fw-normal"><?= esc($item['kode_satuan']) ?></small>
                                    </td>
                                    <td class="text-nowrap">
                                        <?= esc(date('d-m-Y', strtotime($item['berlaku_mulai']))) ?>
                                        <span class="text-secondary">s/d</span>
                                        <?= !empty($item['berlaku_sampai']) ? esc(date('d-m-Y', strtotime($item['berlaku_sampai']))) : '<span class="text-secondary">Tidak dibatasi</span>' ?>
                                    </td>
                                    <td><span class="badge <?= esc($statusBadge) ?>"><?= esc($statusText) ?></span></td>
                                    <?php if ($isAdmin): ?>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-success btn-edit-standard"
                                                    title="Edit Kebutuhan"
                                                    data-id="<?= (int) $item['id_standar'] ?>"
                                                    data-area="<?= (int) $item['id_area'] ?>"
                                                    data-barang="<?= (int) $item['id_barang'] ?>"
                                                    data-jumlah="<?= esc((string) $item['jumlah_standar'], 'attr') ?>"
                                                    data-mulai="<?= esc($item['berlaku_mulai'], 'attr') ?>"
                                                    data-sampai="<?= esc($item['berlaku_sampai'] ?? '', 'attr') ?>"
                                                    data-keterangan="<?= esc($item['keterangan'] ?? '', 'attr') ?>"
                                                    data-status="<?= (int) $item['status'] ?>">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <form method="post" action="<?= base_url('pendataan/master/kebutuhan-area/status/' . (int) $item['id_standar']) ?>"
                                                      onsubmit="return confirm('<?= (int) $item['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?> standar ini?')">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-sm <?= (int) $item['status'] === 1 ? 'btn-outline-warning' : 'btn-outline-primary' ?>"
                                                            title="<?= (int) $item['status'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                        <i class="fa-solid <?= (int) $item['status'] === 1 ? 'fa-ban' : 'fa-check' ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                            <tr id="standardNoResult" class="d-none"><td colspan="<?= $isAdmin ? 7 : 6 ?>" class="text-center text-secondary py-4">Tidak ada data yang cocok dengan pencarian.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white small text-secondary">
                <i class="fa-solid fa-circle-info me-1"></i>
                Standar tidak dihapus permanen. Gunakan Nonaktif untuk menghentikan penggunaan suatu standar tanpa menghilangkan riwayatnya.
            </div>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="standardModal" tabindex="-1" aria-labelledby="standardModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content standard-modal shadow">
            <form id="standardForm" method="post" action="<?= base_url('pendataan/master/kebutuhan-area/simpan') ?>">
                <?= csrf_field() ?>
                <input type="hidden" id="standardId" value="">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="standardModalTitle">
                        <span class="modal-title-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span>
                            <span id="standardModalHeading">Tambah Kebutuhan Area</span>
                            <small class="d-block text-secondary fw-normal mt-1" style="font-size:.75rem">Tentukan barang dan jumlah standar untuk Area terpilih</small>
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="standardArea">Area <span class="text-danger">*</span></label>
                            <select name="id_area" id="standardArea" class="form-select" required>
                                <option value="">Pilih Area</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= (int) $area['id_area'] ?>"><?= esc($area['kode_area'] . ' - ' . $area['nama_area']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="standardBarang">Barang <span class="text-danger">*</span></label>
                            <select name="id_barang" id="standardBarang" class="form-select" required>
                                <option value="">Pilih Barang</option>
                                <?php foreach ($barang as $item): ?>
                                    <option value="<?= (int) $item['id_barang'] ?>"><?= esc($item['kode_barang'] . ' - ' . $item['nama_barang']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="standardJumlah">Jumlah Standar <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_standar" id="standardJumlah" class="form-control" min="0.01" max="999999999999.99" step="0.01" required placeholder="Contoh: 5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="standardStatus">Status</label>
                            <select name="status" id="standardStatus" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="standardMulai">Berlaku Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="berlaku_mulai" id="standardMulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="standardSampai">Berlaku Sampai</label>
                            <input type="date" name="berlaku_sampai" id="standardSampai" class="form-control">
                            <div class="form-text">Kosongkan jika belum ada batas akhir.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="standardKeterangan">Keterangan</label>
                            <textarea name="keterangan" id="standardKeterangan" class="form-control" rows="2" maxlength="5000" placeholder="Keterangan tambahan (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="standard-note mt-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Satu Area dapat memiliki banyak barang standar. Periode standar untuk kombinasi Area dan Barang yang sama tidak boleh bertumpuk.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-save me-1"></i> SIMPAN</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('searchStandard');
    const rows = Array.from(document.querySelectorAll('#standardTableBody tr[data-search]'));
    const noResult = document.getElementById('standardNoResult');
    if (search) search.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach(row => {
            const show = row.dataset.search.includes(term);
            row.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        if (noResult) noResult.classList.toggle('d-none', term === '' || visible > 0);
    });

    const modalElement = document.getElementById('standardModal');
    if (!modalElement) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const form = document.getElementById('standardForm');
    const storeUrl = <?= json_encode(base_url('pendataan/master/kebutuhan-area/simpan')) ?>;
    const updateUrl = <?= json_encode(base_url('pendataan/master/kebutuhan-area/ubah')) ?>;
    const fields = {
        area: document.getElementById('standardArea'),
        barang: document.getElementById('standardBarang'),
        jumlah: document.getElementById('standardJumlah'),
        mulai: document.getElementById('standardMulai'),
        sampai: document.getElementById('standardSampai'),
        keterangan: document.getElementById('standardKeterangan'),
        status: document.getElementById('standardStatus')
    };
    function resetForm() {
        form.reset();
        form.action = storeUrl;
        document.getElementById('standardId').value = '';
        fields.mulai.value = <?= json_encode(date('Y-m-d')) ?>;
        document.getElementById('standardModalHeading').textContent = 'Tambah Kebutuhan Area';
    }
    document.getElementById('btnAddStandard')?.addEventListener('click', resetForm);
    document.querySelectorAll('.btn-edit-standard').forEach(button => button.addEventListener('click', function () {
        resetForm();
        const d = this.dataset;
        fields.area.value = d.area;
        fields.barang.value = d.barang;
        fields.jumlah.value = d.jumlah;
        fields.mulai.value = d.mulai;
        fields.sampai.value = d.sampai;
        fields.keterangan.value = d.keterangan;
        fields.status.value = d.status;
        form.action = updateUrl + '/' + d.id;
        document.getElementById('standardModalHeading').textContent = 'Edit Kebutuhan Area';
        modal.show();
    }));
    modalElement.addEventListener('hidden.bs.modal', resetForm);

    const hasErrors = <?= json_encode(!empty($errors)) ?>;
    const editId = <?= json_encode((int) $editId) ?>;
    const oldValues = {
        area: <?= json_encode((string) old('id_area', '')) ?>,
        barang: <?= json_encode((string) old('id_barang', '')) ?>,
        jumlah: <?= json_encode((string) old('jumlah_standar', '')) ?>,
        mulai: <?= json_encode((string) old('berlaku_mulai', '')) ?>,
        sampai: <?= json_encode((string) old('berlaku_sampai', '')) ?>,
        keterangan: <?= json_encode((string) old('keterangan', '')) ?>,
        status: <?= json_encode((string) old('status', '1')) ?>
    };
    if (hasErrors) {
        resetForm();
        if (editId) {
            const button = document.querySelector('.btn-edit-standard[data-id="' + editId + '"]');
            if (button) {
                const d = button.dataset;
                form.action = updateUrl + '/' + editId;
                document.getElementById('standardModalHeading').textContent = 'Edit Kebutuhan Area';
                fields.area.value = oldValues.area;
                fields.barang.value = oldValues.barang;
                fields.jumlah.value = oldValues.jumlah;
                fields.mulai.value = oldValues.mulai;
                fields.sampai.value = oldValues.sampai;
                fields.keterangan.value = oldValues.keterangan;
                fields.status.value = oldValues.status;
            }
        } else {
            fields.area.value = oldValues.area;
            fields.barang.value = oldValues.barang;
            fields.jumlah.value = oldValues.jumlah;
            fields.mulai.value = oldValues.mulai;
            fields.sampai.value = oldValues.sampai;
            fields.keterangan.value = oldValues.keterangan;
            fields.status.value = oldValues.status;
        }
        modal.show();
    }
});
</script>
<?= $this->endSection() ?>
