<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .trx-intro{color:var(--bs-secondary-color);font-size:.88rem}
    .trx-card{border:1px solid var(--bs-border-color);border-top:3px solid var(--bs-success);border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
    .trx-card>.card-header{background:var(--bs-body-bg);border-bottom:1px solid var(--bs-border-color);padding:.9rem 1.1rem}
    .trx-section-title{font-size:.9rem;font-weight:750;margin:0}
    .trx-form .form-label{font-size:.82rem;font-weight:650;color:#343a40}
    .trx-form .form-control:focus,.trx-form .form-select:focus{border-color:#198754;box-shadow:0 0 0 .2rem rgba(25,135,84,.12)}
    .trx-note{border:1px solid #d8eadf;background:#f3faf5;border-radius:8px;color:#315b40;font-size:.82rem;padding:.75rem .9rem}
    .trx-type-box{border:1px solid var(--bs-border-color);border-radius:8px;padding:.85rem;height:100%;background:var(--bs-body-bg)}
    .trx-table th{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
    .trx-table td{vertical-align:middle}
    .trx-table .form-control,.trx-table .form-select{font-size:.84rem;min-width:130px}
    .trx-step{display:inline-flex;width:25px;height:25px;align-items:center;justify-content:center;background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis);border-radius:50%;font-size:.76rem;font-weight:800;margin-right:.45rem}
    @media(max-width:575.98px){.trx-actions{width:100%}.trx-actions .btn{flex:1}}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$errors = session()->getFlashdata('errors') ?? [];
$oldBarangIds = old('id_barang');
$oldJumlahs = old('jumlah');
if (!is_array($oldBarangIds) || $oldBarangIds === []) $oldBarangIds = [''];
if (!is_array($oldJumlahs)) $oldJumlahs = [];
$rowCount = max(1, count($oldBarangIds));
$oldTanggal = old('tanggal_transaksi') ?: date('Y-m-d\TH:i');
$oldJenis = old('jenis_transaksi') ?: $jenisDefault;
$oldKondisi = old('kondisi') ?: 'BAIK';
$oldAreaAsal = (string) (old('id_area_asal') ?? '');
$oldAreaTujuan = (string) (old('id_area_tujuan') ?? '');
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-8">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Transaksi</div>
                <h1 class="mb-1 fs-3">BUAT TRANSAKSI</h1>
                <div class="trx-intro">Setiap transaksi langsung memperbarui saldo barang setelah disimpan.</div>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end">
                <a href="<?= base_url('pendataan/transaksi') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> KEMBALI
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm">
                <div class="fw-bold mb-1">Transaksi belum dapat disimpan:</div>
                <ul class="mb-0 ps-3"><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (empty($barang)): ?>
            <div class="alert alert-warning shadow-sm">
                Belum ada Master Barang aktif. Tambahkan barang terlebih dahulu sebelum membuat transaksi.
                <a href="<?= base_url('pendataan/master/barang') ?>" class="alert-link">Buka Master Barang</a>.
            </div>
        <?php endif; ?>
        <?php if (empty($areas)): ?>
            <div class="alert alert-warning shadow-sm">
                Belum ada Area aktif. Tambahkan area terlebih dahulu sebelum membuat transaksi.
                <a href="<?= base_url('pendataan/master/area') ?>" class="alert-link">Buka Master Area</a>.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('pendataan/transaksi/simpan') ?>" class="trx-form" id="formTransaksi">
            <?= csrf_field() ?>

            <div class="card trx-card mb-3">
                <div class="card-header"><h2 class="trx-section-title"><span class="trx-step">1</span>Informasi Transaksi</h2></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="jenisTransaksi">Jenis Transaksi <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenisTransaksi" name="jenis_transaksi" required>
                                <?php foreach ($jenisTransaksi as $kode => $label): ?>
                                    <option value="<?= esc($kode) ?>" <?= $oldJenis === $kode ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="tanggalTransaksi">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="tanggalTransaksi" name="tanggal_transaksi" value="<?= esc($oldTanggal) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="kondisiBarang">Kondisi Barang <span class="text-danger">*</span></label>
                            <select class="form-select" id="kondisiBarang" name="kondisi" required>
                                <?php foreach ($kondisiBarang as $kondisi): ?>
                                    <?php $label = $kondisi === 'DALAM_PERBAIKAN' ? 'Dalam Perbaikan' : ucwords(strtolower(str_replace('_', ' ', $kondisi))); ?>
                                    <option value="<?= esc($kondisi) ?>" <?= $oldKondisi === $kondisi ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-secondary">Kondisi yang dipilih berlaku untuk seluruh barang pada transaksi ini.</small>
                        </div>
                        <div class="col-md-6" id="areaAsalWrapper">
                            <label class="form-label" for="areaAsal">Area Asal <span class="text-danger">*</span></label>
                            <select class="form-select" id="areaAsal" name="id_area_asal">
                                <option value="">Pilih Area Asal</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= (int) $area['id_area'] ?>" <?= $oldAreaAsal === (string) $area['id_area'] ? 'selected' : '' ?>>
                                        <?= esc($area['kode_area'] . ' - ' . $area['nama_area']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6" id="areaTujuanWrapper">
                            <label class="form-label" for="areaTujuan" id="areaTujuanLabel">Area Tujuan <span class="text-danger">*</span></label>
                            <select class="form-select" id="areaTujuan" name="id_area_tujuan">
                                <option value="">Pilih Area Tujuan</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= (int) $area['id_area'] ?>" <?= $oldAreaTujuan === (string) $area['id_area'] ? 'selected' : '' ?>>
                                        <?= esc($area['kode_area'] . ' - ' . $area['nama_area']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="2" maxlength="5000" placeholder="Catatan tambahan transaksi (opsional)"><?= esc(old('keterangan') ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card trx-card mb-3">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h2 class="trx-section-title"><span class="trx-step">2</span>Detail Barang</h2>
                    <button type="button" class="btn btn-outline-success btn-sm fw-bold" id="addDetail">
                        <i class="fa-solid fa-plus me-1"></i> TAMBAH BARANG
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table trx-table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:55px">No</th>
                                    <th style="min-width:300px">Barang</th>
                                    <th style="width:190px">Jumlah</th>
                                    <th class="text-center" style="width:75px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detailBody">
                                <?php for ($i = 0; $i < $rowCount; $i++): ?>
                                    <tr class="detail-row">
                                        <td class="text-center detail-number text-secondary"><?= $i + 1 ?></td>
                                        <td>
                                            <select name="id_barang[]" class="form-select" required>
                                                <option value="">Pilih Barang</option>
                                                <?php foreach ($barang as $item): ?>
                                                    <option value="<?= (int) $item['id_barang'] ?>" <?= (string) ($oldBarangIds[$i] ?? '') === (string) $item['id_barang'] ? 'selected' : '' ?>>
                                                        <?= esc($item['kode_barang'] . ' - ' . $item['nama_barang']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" name="jumlah[]" class="form-control" min="0.01" max="999999999999.99" step="0.01" value="<?= esc($oldJumlahs[$i] ?? '') ?>" required placeholder="0.00"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-detail" title="Hapus baris" <?= $rowCount === 1 ? 'disabled' : '' ?>>
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white small text-secondary">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Satuan barang mengikuti Master Barang. Jumlah wajib lebih dari nol.
                </div>
            </div>

            <div class="trx-note mb-3" id="transactionNote">
                <i class="fa-solid fa-circle-info me-1"></i>
                Saldo akan langsung diperbarui setelah transaksi disimpan. Pastikan Area, barang, jumlah, dan kondisinya sudah benar.
            </div>

            <div class="d-flex justify-content-end gap-2 flex-wrap trx-actions">
                <a href="<?= base_url('pendataan/transaksi') ?>" class="btn btn-light border">BATAL</a>
                <button type="submit" class="btn btn-success fw-bold" id="saveDraft" <?= empty($barang) || empty($areas) ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-floppy-disk me-1"></i> <span id="saveButtonLabel">SIMPAN TRANSAKSI</span>
                </button>
            </div>
        </form>
    </div>
</div>

<template id="detailRowTemplate">
    <tr class="detail-row">
        <td class="text-center detail-number text-secondary"></td>
        <td>
            <select name="id_barang[]" class="form-select" required>
                <option value="">Pilih Barang</option>
                <?php foreach ($barang as $item): ?>
                    <option value="<?= (int) $item['id_barang'] ?>"><?= esc($item['kode_barang'] . ' - ' . $item['nama_barang']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" name="jumlah[]" class="form-control" min="0.01" max="999999999999.99" step="0.01" required placeholder="0.00"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-detail" title="Hapus baris">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('jenisTransaksi');
    const sourceWrap = document.getElementById('areaAsalWrapper');
    const targetWrap = document.getElementById('areaTujuanWrapper');
    const source = document.getElementById('areaAsal');
    const target = document.getElementById('areaTujuan');
    const body = document.getElementById('detailBody');
    const template = document.getElementById('detailRowTemplate');
    const addButton = document.getElementById('addDetail');

    function updateTypeFields() {
        const value = type.value;
        const needSource = value === 'PENGELUARAN' || value === 'TRANSFER';
        const needTarget = value === 'SALDO_AWAL' || value === 'PENERIMAAN' || value === 'TRANSFER';
        sourceWrap.classList.toggle('d-none', !needSource);
        targetWrap.classList.toggle('d-none', !needTarget);
        source.required = needSource;
        target.required = needTarget;
        document.getElementById('areaTujuanLabel').innerHTML = value === 'SALDO_AWAL' ? 'Area Pencatatan Stok <span class="text-danger">*</span>' : 'Area Tujuan <span class="text-danger">*</span>';

        const descriptions = {
            SALDO_AWAL: 'Saldo awal akan langsung ditambahkan ke Area yang dipilih.',
            PENERIMAAN: 'Barang Masuk akan langsung menambah saldo Area tujuan.',
            PENGELUARAN: 'Barang Keluar akan langsung mengurangi saldo Area asal. Stok harus mencukupi.',
            TRANSFER: 'Barang akan langsung dipindahkan. Saldo Area asal harus mencukupi.',
        };
        const labels = {
            SALDO_AWAL: 'CATAT SALDO AWAL',
            PENERIMAAN: 'SIMPAN BARANG MASUK',
            PENGELUARAN: 'SIMPAN BARANG KELUAR',
            TRANSFER: 'PINDAHKAN BARANG',
        };
        document.getElementById('transactionNote').textContent = descriptions[value]
            || 'Saldo akan langsung diperbarui setelah transaksi disimpan.';
        document.getElementById('saveButtonLabel').textContent = labels[value] || 'SIMPAN TRANSAKSI';

        if (!needSource) source.value = '';
        if (!needTarget) target.value = '';
    }

    function updateRows() {
        const rows = Array.from(body.querySelectorAll('.detail-row'));
        rows.forEach((row, index) => {
            row.querySelector('.detail-number').textContent = index + 1;
            row.querySelector('.remove-detail').disabled = rows.length === 1;
        });
    }

    addButton.addEventListener('click', function () {
        body.appendChild(template.content.cloneNode(true));
        updateRows();
    });

    body.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-detail');
        if (!button || body.querySelectorAll('.detail-row').length <= 1) return;
        button.closest('.detail-row').remove();
        updateRows();
    });

    type.addEventListener('change', updateTypeFields);
    updateTypeFields();
    updateRows();
});
</script>
<?= $this->endSection() ?>
