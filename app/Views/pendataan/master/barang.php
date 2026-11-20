<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .master-intro{color:var(--bs-secondary-color);font-size:.88rem}
    .master-card{border:1px solid var(--bs-border-color);border-top:3px solid var(--bs-success);border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
    .master-card .card-header{background:var(--bs-body-bg);border-bottom:1px solid var(--bs-border-color);padding:.85rem 1rem}
    .master-table th{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
    .master-table td{vertical-align:middle;font-size:.86rem}
    .item-code{display:inline-flex;padding:.32rem .55rem;border-radius:7px;background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis);font-weight:700;font-size:.78rem}
    .item-photo{width:42px;height:42px;object-fit:cover;border-radius:7px;border:1px solid var(--bs-border-color)}
    .item-modal .modal-content{border:0;border-radius:14px;overflow:hidden}
    .item-modal .modal-header{padding:1.1rem 1.35rem;background:#fff}
    .item-modal .modal-title{display:flex;align-items:center;gap:.75rem;font-size:1.05rem;font-weight:700;color:#212529}
    .item-modal .modal-title-icon{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;background:#e8f5ee;color:#198754;font-size:1.1rem;flex-shrink:0}
    .item-modal .modal-body{padding:1.35rem}
    .item-modal .form-label{font-size:.88rem;font-weight:600;color:#343a40}
    .item-modal .form-control:focus,.item-modal .form-select:focus{border-color:#198754;box-shadow:0 0 0 .2rem rgba(25,135,84,.12)}
    .item-modal .modal-footer{padding:.95rem 1.35rem;background:#fbfcfb}
    @media(max-width:575.98px){.item-search{max-width:none!important;width:100%}}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$errors = session()->getFlashdata('errors') ?? [];
$editBarangId = session()->getFlashdata('edit_barang_id') ?? old('id_barang');
$isAdmin = \App\Libraries\Roles::has('admin');
?>
<div class="app-content-header"><div class="container-fluid"><div class="row align-items-center g-2">
    <div class="col-md-7">
        <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Master Data</div>
        <h1 class="mb-1 fs-3">MASTER BARANG</h1>
        <div class="master-intro">Kelola identitas barang, kategori, satuan, dan informasi spesifikasinya.</div>
    </div>
    <div class="col-md-5 d-flex justify-content-md-end gap-2 flex-wrap">
        <a href="<?= base_url('pendataan') ?>" class="btn btn-outline-secondary btn-sm fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> DASHBOARD</a>
        <?php if($isAdmin): ?><button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#barangModal"><i class="fa-solid fa-plus me-1"></i> TAMBAH BARANG</button><?php endif; ?>
    </div>
</div></div></div>
<div class="app-content"><div class="container-fluid">
    <?php if(session()->getFlashdata('success')): ?><div class="alert alert-success shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?><div class="alert alert-danger shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php if(!empty($errors)): ?><div class="alert alert-danger shadow-sm"><div class="fw-bold mb-1">Periksa kembali isian berikut:</div><ul class="mb-0 ps-3"><?php foreach($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="card master-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h2 class="h6 fw-bold mb-0">Daftar Barang</h2><div class="small text-secondary mt-1"><?= count($items) ?> barang terdaftar</div></div>
            <div class="input-group input-group-sm item-search" style="max-width:300px"><span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span><input type="search" id="searchBarang" class="form-control" placeholder="Cari kode, nama, kategori..."></div>
        </div>
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table table-hover master-table mb-0"><thead><tr>
                <th class="text-center" style="width:55px">No</th><th style="width:65px">Foto</th><th>Kode Barang</th><th>Nama Barang</th><th>Kategori</th><th>Satuan</th><th>Merek / Model</th><th class="text-center">Status</th><?php if($isAdmin): ?><th class="text-end">Aksi</th><?php endif; ?>
            </tr></thead><tbody id="barangTableBody">
            <?php if(empty($items)): ?><tr><td colspan="<?= $isAdmin?9:8 ?>" class="text-center text-secondary py-5"><i class="fa-solid fa-box-open fa-2x mb-2 d-block opacity-50"></i>Belum ada data barang.</td></tr>
            <?php else: foreach($items as $i=>$item): $search=strtolower(implode(' ',[$item['kode_barang'],$item['nama_barang'],$item['nama_kategori'],$item['nama_satuan'],$item['merek']??'',$item['model_tipe']??''])); ?>
                <tr data-search="<?= esc($search,'attr') ?>">
                    <td class="text-center text-secondary"><?= $i+1 ?></td>
                    <td><?php if(!empty($item['foto_barang'])): ?><img class="item-photo" src="<?= base_url('uploads/pendataan/barang/'.rawurlencode($item['foto_barang'])) ?>" alt="Foto barang"><?php else: ?><span class="item-photo d-inline-flex align-items-center justify-content-center text-secondary"><i class="fa-solid fa-image"></i></span><?php endif; ?></td>
                    <td><span class="item-code"><?= esc($item['kode_barang']) ?></span></td><td class="fw-semibold"><?= esc($item['nama_barang']) ?></td>
                    <td><?= esc($item['nama_kategori']) ?></td><td><?= esc($item['nama_satuan']) ?></td>
                    <td class="text-secondary"><?= esc(trim(($item['merek']??'').' '.($item['model_tipe']??'')) ?: '-') ?></td>
                    <td class="text-center"><?php if((int)$item['status']===1): ?><span class="badge text-bg-success">Aktif</span><?php else: ?><span class="badge text-bg-secondary">Nonaktif</span><?php endif; ?></td>
                    <?php if($isAdmin): ?><td class="text-end"><div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-success btn-edit-barang" title="Edit Barang"
                            data-id="<?= (int)$item['id_barang'] ?>" data-kode="<?= esc($item['kode_barang'],'attr') ?>" data-nama="<?= esc($item['nama_barang'],'attr') ?>"
                            data-kategori="<?= (int)$item['id_kategori'] ?>" data-satuan="<?= (int)$item['id_satuan'] ?>" data-merek="<?= esc($item['merek']??'','attr') ?>"
                            data-model="<?= esc($item['model_tipe']??'','attr') ?>" data-spesifikasi="<?= esc($item['spesifikasi']??'','attr') ?>"
                            data-deskripsi="<?= esc($item['deskripsi']??'','attr') ?>" data-status="<?= (int)$item['status'] ?>">
                            <i class="fa-solid fa-pen"></i></button>
                        <form method="post" action="<?= base_url('pendataan/master/barang/status/'.(int)$item['id_barang']) ?>" onsubmit="return confirm('<?= (int)$item['status']===1?'Nonaktifkan':'Aktifkan' ?> barang ini?')"><?= csrf_field() ?>
                            <button class="btn btn-sm <?= (int)$item['status']===1?'btn-outline-warning':'btn-outline-primary' ?>" title="<?= (int)$item['status']===1?'Nonaktifkan':'Aktifkan' ?>"><i class="fa-solid <?= (int)$item['status']===1?'fa-ban':'fa-check' ?>"></i></button>
                        </form>
                    </div></td><?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
            <tr id="barangNoResult" class="d-none"><td colspan="<?= $isAdmin?9:8 ?>" class="text-center text-secondary py-4">Tidak ada barang yang cocok dengan pencarian.</td></tr>
            </tbody></table>
        </div></div>
        <div class="card-footer bg-white small text-secondary"><i class="fa-solid fa-circle-info me-1"></i>Barang yang sudah tercatat pada transaksi tidak dihapus permanen. Gunakan status Nonaktif bila tidak digunakan lagi.</div>
    </div>
</div></div>

<?php if($isAdmin): ?>
<div class="modal fade" id="barangModal" tabindex="-1" aria-labelledby="barangModalTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content item-modal shadow">
<form id="barangForm" method="post" enctype="multipart/form-data" action="<?= base_url('pendataan/master/barang/simpan') ?>"><?= csrf_field() ?>
    <input type="hidden" id="barangId" value="">
    <div class="modal-header border-bottom"><h5 class="modal-title" id="barangModalTitle"><span class="modal-title-icon"><i class="fa-solid fa-box"></i></span><span><span id="barangModalHeading">Tambah Barang</span><small class="d-block text-secondary fw-normal mt-1" style="font-size:.75rem">Lengkapi identitas dan informasi barang</small></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Kode Barang <span class="text-danger">*</span></label><input type="text" name="kode_barang" id="barangKode" class="form-control" maxlength="30" required placeholder="Contoh: BRG-0001"></div>
        <div class="col-md-6"><label class="form-label">Nama Barang <span class="text-danger">*</span></label><input type="text" name="nama_barang" id="barangNama" class="form-control" maxlength="150" required></div>
        <div class="col-md-6"><label class="form-label">Kategori <span class="text-danger">*</span></label><select name="id_kategori" id="barangKategori" class="form-select" required><option value="">Pilih Kategori</option><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id_kategori'] ?>"><?= esc($cat['kode_kategori'].' - '.$cat['nama_kategori']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Satuan <span class="text-danger">*</span></label><select name="id_satuan" id="barangSatuan" class="form-select" required><option value="">Pilih Satuan</option><?php foreach($units as $unit): ?><option value="<?= (int)$unit['id_satuan'] ?>"><?= esc($unit['kode_satuan'].' - '.$unit['nama_satuan']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Merek</label><input type="text" name="merek" id="barangMerek" class="form-control" maxlength="100"></div>
        <div class="col-md-6"><label class="form-label">Model / Tipe</label><input type="text" name="model_tipe" id="barangModel" class="form-control" maxlength="100"></div>
        <div class="col-12"><label class="form-label">Spesifikasi</label><textarea name="spesifikasi" id="barangSpesifikasi" class="form-control" rows="2"></textarea></div>
        <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="deskripsi" id="barangDeskripsi" class="form-control" rows="2"></textarea></div>
        <div class="col-md-8"><label class="form-label">Foto Barang</label><input type="file" name="foto_barang" class="form-control" accept=".jpg,.jpeg,.png,.webp"><small class="text-secondary">Format JPG, PNG, WEBP. Maksimal 2 MB. Kosongkan saat edit jika foto tidak diganti.</small></div>
        <div class="col-md-4"><label class="form-label">Status</label><select name="status" id="barangStatus" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-save me-1"></i> SIMPAN</button></div>
</form></div></div></div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('searchBarang');
    const rows = Array.from(document.querySelectorAll('#barangTableBody tr[data-search]'));
    const noResult = document.getElementById('barangNoResult');
    if (search) search.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach(row => { const show = row.dataset.search.includes(term); row.classList.toggle('d-none', !show); if (show) visible++; });
        if (noResult) noResult.classList.toggle('d-none', visible !== 0 || rows.length === 0);
    });

    const modalEl = document.getElementById('barangModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const form = document.getElementById('barangForm');
    const fields = {
        id: document.getElementById('barangId'), kode: document.getElementById('barangKode'),
        nama: document.getElementById('barangNama'), kategori: document.getElementById('barangKategori'),
        satuan: document.getElementById('barangSatuan'), merek: document.getElementById('barangMerek'),
        model: document.getElementById('barangModel'), spesifikasi: document.getElementById('barangSpesifikasi'),
        deskripsi: document.getElementById('barangDeskripsi'), status: document.getElementById('barangStatus')
    };
    function resetForm() {
        form.reset(); fields.id.value = ''; form.action = '<?= base_url('pendataan/master/barang/simpan') ?>';
        document.getElementById('barangModalHeading').textContent = 'Tambah Barang';
    }
    document.querySelectorAll('.btn-edit-barang').forEach(button => button.addEventListener('click', function () {
        resetForm();
        const d = this.dataset;
        fields.id.value = d.id; fields.kode.value = d.kode; fields.nama.value = d.nama;
        fields.kategori.value = d.kategori; fields.satuan.value = d.satuan; fields.merek.value = d.merek;
        fields.model.value = d.model; fields.spesifikasi.value = d.spesifikasi; fields.deskripsi.value = d.deskripsi;
        fields.status.value = d.status;
        form.action = '<?= base_url('pendataan/master/barang/ubah') ?>/' + d.id;
        document.getElementById('barangModalHeading').textContent = 'Edit Barang';
        modal.show();
    }));
    modalEl.addEventListener('hidden.bs.modal', resetForm);
    <?php if($editBarangId): ?>modal.show();<?php endif; ?>
});
</script>
<?= $this->endSection() ?>
