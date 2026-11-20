<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .trx-intro{color:var(--bs-secondary-color);font-size:.88rem}
    .trx-card{border:1px solid var(--bs-border-color);border-top:3px solid var(--bs-success);border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
    .trx-card .card-header{background:var(--bs-body-bg);border-bottom:1px solid var(--bs-border-color);padding:.85rem 1rem}
    .trx-type-card{height:100%;border:1px solid var(--bs-border-color);border-radius:9px;background:var(--bs-body-bg)}
    .trx-type-icon{width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;border-radius:9px;background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis);font-size:1rem}
    .trx-count{font-size:1.35rem;font-weight:750;line-height:1.2}
    .trx-label{font-size:.72rem;color:var(--bs-secondary-color);text-transform:uppercase;letter-spacing:.04em;font-weight:700}
    .trx-table th{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
    .trx-table td{vertical-align:middle;font-size:.86rem}
    .trx-number{font-weight:700;color:var(--bs-success-text-emphasis)}
    .trx-filter .form-label{font-size:.78rem;font-weight:650;color:var(--bs-secondary-color);margin-bottom:.3rem}
    .trx-filter .form-control,.trx-filter .form-select{font-size:.84rem}
    @media(max-width:575.98px){.trx-filter .filter-search{width:100%}}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$jenisLabel = [
    'PENERIMAAN' => 'Barang Masuk',
    'PENGELUARAN' => 'Barang Keluar',
    'TRANSFER' => 'Pindah Area',
];
$statusLabel = [
    'DRAFT' => 'Draft',
    'DIAJUKAN' => 'Diajukan',
    'DISETUJUI' => 'Disetujui',
    'DITOLAK' => 'Ditolak',
    'DIPOSTING' => 'Diposting',
    'DIBATALKAN' => 'Dibatalkan',
];
$statusClass = [
    'DRAFT' => 'text-bg-secondary',
    'DIAJUKAN' => 'text-bg-primary',
    'DISETUJUI' => 'text-bg-info',
    'DITOLAK' => 'text-bg-danger',
    'DIPOSTING' => 'text-bg-success',
    'DIBATALKAN' => 'text-bg-dark',
];
$totalTransaksi = array_sum($ringkasan);
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-8">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Transaksi</div>
                <h1 class="mb-1 fs-3">TRANSAKSI BARANG</h1>
                <div class="trx-intro">Pantau riwayat barang masuk, barang keluar, dan perpindahan barang antar area.</div>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end">
                <a href="<?= base_url('pendataan') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> DASHBOARD
                </a>
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

        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="trx-type-card p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="trx-label">Total Transaksi</span>
                        <span class="trx-type-icon"><i class="fa-solid fa-list-check"></i></span>
                    </div>
                    <div class="trx-count"><?= number_format($totalTransaksi) ?></div>
                    <div class="small text-secondary mt-1">Semua status</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="trx-type-card p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="trx-label">Draft</span>
                        <span class="trx-type-icon"><i class="fa-regular fa-file-lines"></i></span>
                    </div>
                    <div class="trx-count"><?= number_format($ringkasan['DRAFT']) ?></div>
                    <div class="small text-secondary mt-1">Belum diajukan</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="trx-type-card p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="trx-label">Diajukan</span>
                        <span class="trx-type-icon"><i class="fa-regular fa-paper-plane"></i></span>
                    </div>
                    <div class="trx-count"><?= number_format($ringkasan['DIAJUKAN']) ?></div>
                    <div class="small text-secondary mt-1">Menunggu proses</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="trx-type-card p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="trx-label">Disetujui</span>
                        <span class="trx-type-icon"><i class="fa-regular fa-circle-check"></i></span>
                    </div>
                    <div class="trx-count"><?= number_format($ringkasan['DISETUJUI']) ?></div>
                    <div class="small text-secondary mt-1">Siap diproses</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="trx-type-card p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="trx-label">Diposting</span>
                        <span class="trx-type-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                    </div>
                    <div class="trx-count"><?= number_format($ringkasan['DIPOSTING']) ?></div>
                    <div class="small text-secondary mt-1">Sudah diproses</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="trx-type-card p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="trx-label">Ditolak</span>
                        <span class="trx-type-icon"><i class="fa-solid fa-ban"></i></span>
                    </div>
                    <div class="trx-count"><?= number_format($ringkasan['DITOLAK']) ?></div>
                    <div class="small text-secondary mt-1">Perlu ditinjau</div>
                </div>
            </div>
        </div>

        <div class="card trx-card mb-3">
            <div class="card-header">
                <h2 class="h6 fw-bold mb-0">Jenis Transaksi</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="trx-type-icon"><i class="fa-solid fa-arrow-down-to-bracket"></i></span>
                            <div><div class="fw-bold">Barang Masuk</div><div class="small text-secondary">Penerimaan barang ke area penyimpanan atau pemakai.</div></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="trx-type-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></span>
                            <div><div class="fw-bold">Barang Keluar</div><div class="small text-secondary">Pengeluaran barang dari area.</div></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="trx-type-icon"><i class="fa-solid fa-right-left"></i></span>
                            <div><div class="fw-bold">Pindah Area</div><div class="small text-secondary">Pemindahan barang dari satu area ke area lain.</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card trx-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="h6 fw-bold mb-0">Riwayat Transaksi</h2>
                    <div class="small text-secondary mt-1"><?= count($transaksi) ?> data ditampilkan</div>
                </div>
            </div>
            <div class="card-body border-bottom">
                <form method="get" action="<?= base_url('pendataan/transaksi') ?>" class="row g-2 align-items-end trx-filter">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Pencarian</label>
                        <input type="search" name="q" class="form-control" value="<?= esc($filter['q']) ?>" placeholder="Nomor, pembuat, keterangan...">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" class="form-select">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($jenisTransaksi as $kode => $label): ?>
                                <option value="<?= esc($kode) ?>" <?= $filter['jenis'] === $kode ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <?php foreach ($statusTransaksi as $kode): ?>
                                <option value="<?= esc($kode) ?>" <?= $filter['status'] === $kode ? 'selected' : '' ?>><?= esc($statusLabel[$kode]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="<?= esc($filter['tanggal_mulai']) ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="<?= esc($filter['tanggal_selesai']) ?>">
                    </div>
                    <div class="col-12 col-md-1 d-flex gap-2">
                        <button class="btn btn-success btn-sm fw-bold flex-grow-1" title="Terapkan filter"><i class="fa-solid fa-search"></i></button>
                        <a href="<?= base_url('pendataan/transaksi') ?>" class="btn btn-outline-secondary btn-sm" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover trx-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:55px">No</th>
                                <th>Nomor Transaksi</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Dibuat Oleh</th>
                                <th>Alur</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($transaksi)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-5">
                                    <i class="fa-solid fa-receipt fa-2x mb-2 d-block opacity-50"></i>
                                    Belum ada transaksi yang sesuai dengan filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transaksi as $i => $row): ?>
                                <tr>
                                    <td class="text-center text-secondary"><?= $i + 1 ?></td>
                                    <td><span class="trx-number"><?= esc($row['nomor_transaksi']) ?></span></td>
                                    <td class="text-nowrap"><?= esc(date('d-m-Y H:i', strtotime($row['tanggal_transaksi']))) ?></td>
                                    <td><?= esc($jenisLabel[$row['jenis_transaksi']] ?? $row['jenis_transaksi']) ?></td>
                                    <td><?= esc($row['nama_pembuat'] ?: '-') ?></td>
                                    <td>
                                        <?php if ($row['alur_persetujuan'] === 'LANGSUNG'): ?>
                                            <span class="badge text-bg-secondary">Langsung</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-primary">Approval</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge <?= esc($statusClass[$row['status_transaksi']] ?? 'text-bg-secondary') ?>"><?= esc($statusLabel[$row['status_transaksi']] ?? $row['status_transaksi']) ?></span></td>
                                    <td class="text-secondary"><?= esc($row['keterangan'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white small text-secondary">
                <i class="fa-solid fa-circle-info me-1"></i>
                Halaman ini menampilkan riwayat dan status transaksi. Form input, persetujuan, dan pembaruan saldo akan dibuat mengikuti alur kerja yang ditetapkan.
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
