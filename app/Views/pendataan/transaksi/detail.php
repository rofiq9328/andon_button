<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .trx-intro{color:var(--bs-secondary-color);font-size:.88rem}
    .trx-card{border:1px solid var(--bs-border-color);border-top:3px solid var(--bs-success);border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
    .trx-card>.card-header{background:var(--bs-body-bg);border-bottom:1px solid var(--bs-border-color);padding:.9rem 1.1rem}
    .trx-label{font-size:.72rem;font-weight:750;color:var(--bs-secondary-color);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem}
    .trx-value{font-size:.9rem;font-weight:600;color:var(--bs-body-color);overflow-wrap:anywhere}
    .trx-number{font-size:1.08rem;font-weight:800;color:var(--bs-success-text-emphasis)}
    .trx-table th{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
    .trx-table td{vertical-align:middle;font-size:.86rem}
    .trx-info{border:1px solid #d8eadf;background:#f3faf5;border-radius:8px;color:#315b40;font-size:.82rem;padding:.8rem .95rem}
    .trx-section-title{font-size:.9rem;font-weight:750;margin:0}
    .trx-meta{background:var(--bs-tertiary-bg);border-radius:8px;padding:.9rem;height:100%}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$jenisLabel = [
    'SALDO_AWAL' => 'Saldo Awal',
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
$kondisiLabel = [
    'BAIK' => 'Baik',
    'RUSAK' => 'Rusak',
    'DALAM_PERBAIKAN' => 'Dalam Perbaikan',
    'TIDAK_LAYAK' => 'Tidak Layak',
];
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-8">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Transaksi / Detail</div>
                <h1 class="mb-1 fs-3">DETAIL TRANSAKSI</h1>
                <div class="trx-intro">Informasi transaksi dan rincian barang yang tercatat.</div>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end">
                <a href="<?= base_url('pendataan/transaksi') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> KEMBALI KE TRANSAKSI
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
        <div class="card trx-card mb-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="trx-label mb-1">Nomor Transaksi</div>
                    <div class="trx-number"><?= esc($transaksi['nomor_transaksi']) ?></div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge text-bg-light border"><?= esc($jenisLabel[$transaksi['jenis_transaksi']] ?? $transaksi['jenis_transaksi']) ?></span>
                    <span class="badge <?= esc($statusClass[$transaksi['status_transaksi']] ?? 'text-bg-secondary') ?>"><?= esc($statusLabel[$transaksi['status_transaksi']] ?? $transaksi['status_transaksi']) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="trx-label">Tanggal Transaksi</div>
                        <div class="trx-value"><?= esc(date('d-m-Y H:i', strtotime($transaksi['tanggal_transaksi']))) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="trx-label">Jenis Transaksi</div>
                        <div class="trx-value"><?= esc($jenisLabel[$transaksi['jenis_transaksi']] ?? $transaksi['jenis_transaksi']) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="trx-label">Alur Persetujuan</div>
                        <div class="trx-value"><?= $transaksi['alur_persetujuan'] === 'LANGSUNG' ? 'Langsung' : 'Approval' ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="trx-label">Dibuat Oleh</div>
                        <div class="trx-value"><?= esc($transaksi['nama_pembuat'] ?: '-') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="trx-label">Keterangan</div>
                        <div class="trx-value fw-normal"><?= nl2br(esc($transaksi['keterangan'] ?: '-')) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card trx-card mb-3">
            <div class="card-header">
                <h2 class="trx-section-title">Rincian Barang</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover trx-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:55px">No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Jumlah</th>
                                <th>Satuan</th>
                                <th>Area Asal</th>
                                <th>Area Tujuan</th>
                                <th>Kondisi Asal</th>
                                <th>Kondisi Tujuan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($detail)): ?>
                            <tr><td colspan="9" class="text-center text-secondary py-5">Tidak ada rincian barang pada transaksi ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($detail as $i => $row): ?>
                                <tr>
                                    <td class="text-center text-secondary"><?= $i + 1 ?></td>
                                    <td class="fw-semibold text-nowrap"><?= esc($row['kode_barang']) ?></td>
                                    <td><?= esc($row['nama_barang']) ?></td>
                                    <td class="text-end fw-bold"><?= esc(number_format((float) $row['jumlah'], 2, ',', '.')) ?></td>
                                    <td><?= esc($row['kode_satuan']) ?></td>
                                    <td><?= !empty($row['kode_area_asal']) ? esc($row['kode_area_asal'] . ' - ' . $row['nama_area_asal']) : '-' ?></td>
                                    <td><?= !empty($row['kode_area_tujuan']) ? esc($row['kode_area_tujuan'] . ' - ' . $row['nama_area_tujuan']) : '-' ?></td>
                                    <td><?= esc($kondisiLabel[$row['kondisi_asal']] ?? '-') ?></td>
                                    <td><?= esc($kondisiLabel[$row['kondisi_tujuan']] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white small text-secondary">
                Total baris barang: <strong><?= count($detail) ?></strong>
            </div>
        </div>

        <div class="card trx-card mb-3">
            <div class="card-header"><h2 class="trx-section-title">Riwayat Proses</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-lg-6"><div class="trx-meta">
                        <div class="trx-label">Dicatat Oleh</div><div class="trx-value"><?= esc($transaksi['nama_pembuat'] ?: '-') ?></div>
                    </div></div>
                    <div class="col-6 col-lg-6"><div class="trx-meta">
                        <div class="trx-label">Saldo Diperbarui Oleh</div><div class="trx-value"><?= esc($transaksi['nama_pemosting'] ?: '-') ?></div>
                    </div></div>
                </div>
                <?php if (!empty($transaksi['alasan_penolakan'])): ?>
                    <div class="alert alert-secondary mt-3 mb-0">
                        <div class="fw-bold mb-1">Catatan Historis</div>
                        <?= nl2br(esc($transaksi['alasan_penolakan'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($transaksi['status_transaksi'] === 'DIPOSTING'): ?>
            <div class="trx-info">
                <i class="fa-solid fa-circle-check me-1"></i>
                Saldo barang sudah diperbarui secara langsung saat transaksi ini disimpan.
            </div>
        <?php else: ?>
            <div class="alert alert-warning small mb-0">
                Transaksi ini berasal dari alur lama sebelum pembaruan. Data historisnya tetap disimpan, tetapi saldo tidak otomatis diubah.
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
