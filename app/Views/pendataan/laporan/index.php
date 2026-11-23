<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .report-intro{color:var(--bs-secondary-color);font-size:.88rem}
    .report-card{border:1px solid var(--bs-border-color);border-top:3px solid var(--bs-success);border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
    .report-card>.card-header{background:var(--bs-body-bg);border-bottom:1px solid var(--bs-border-color);padding:.9rem 1rem}
    .report-stat{height:100%;border:1px solid var(--bs-border-color);border-radius:9px;padding:.95rem;background:var(--bs-body-bg)}
    .report-stat-label{font-size:.7rem;font-weight:700;color:var(--bs-secondary-color);text-transform:uppercase;letter-spacing:.04em}
    .report-stat-value{font-size:1.35rem;font-weight:800;line-height:1.2;margin-top:.25rem;font-variant-numeric:tabular-nums}
    .report-filter .form-label{font-size:.76rem;font-weight:650;color:var(--bs-secondary-color);margin-bottom:.3rem}
    .report-table th{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.68rem;font-weight:750;text-transform:uppercase;letter-spacing:.035em;white-space:nowrap;vertical-align:middle}
    .report-table td{vertical-align:middle;font-size:.8rem}
    .report-code{display:inline-flex;padding:.22rem .42rem;border-radius:6px;background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis);font-size:.68rem;font-weight:700}
    .report-number{font-variant-numeric:tabular-nums;white-space:nowrap}
    .report-empty{padding:3rem 1rem;text-align:center;color:var(--bs-secondary-color)}
    .report-tabs .nav-link{font-size:.82rem;font-weight:700;color:var(--bs-secondary-color);border:1px solid var(--bs-border-color);background:var(--bs-body-bg);margin-right:.35rem}
    .report-tabs .nav-link.active{background:var(--bs-success);border-color:var(--bs-success);color:#fff}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$typeLabels = [
    'saldo' => 'Saldo per Area',
    'transaksi' => 'Barang Masuk/Keluar',
    'mutasi' => 'Mutasi Pindah Area',
];
$fungsiLabels = [
    'PENGGUNA' => 'Area Pengguna',
    'PENYIMPANAN' => 'Penyimpanan',
    'KEDUANYA' => 'Keduanya',
];
$formatQty = static function ($value) {
    return number_format((float) $value, 2, ',', '.');
};
$exportUrl = base_url('pendataan/laporan/export') . '?' . http_build_query($filter);
$tabUrl = static function ($type) use ($filter) {
    $params = $filter;
    $params['jenis_laporan'] = $type;
    return base_url('pendataan/laporan') . '?' . http_build_query($params);
};
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-8">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Laporan</div>
                <h1 class="mb-1 fs-3">LAPORAN BARANG</h1>
                <div class="report-intro">Rekap saldo aktual dan riwayat pergerakan barang berdasarkan transaksi yang sudah memperbarui saldo.</div>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end gap-2">
                <a href="<?= base_url('pendataan') ?>" class="btn btn-outline-secondary btn-sm fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> DASHBOARD</a>
                <a href="<?= esc($exportUrl, 'attr') ?>" class="btn btn-success btn-sm fw-bold"><i class="fa-solid fa-file-csv me-1"></i> EXPORT CSV</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card report-card mb-3">
            <div class="card-body">
                <ul class="nav nav-pills report-tabs flex-wrap gap-1 mb-3">
                    <?php foreach ($typeLabels as $key => $label): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $jenisLaporan === $key ? 'active' : '' ?>" href="<?= esc($tabUrl($key), 'attr') ?>">
                                <?= esc($label) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="get" action="<?= base_url('pendataan/laporan') ?>" class="row g-2 align-items-end report-filter">
                    <input type="hidden" name="jenis_laporan" value="<?= esc($jenisLaporan) ?>">
                    <div class="col-12 col-md-3">
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
                    <div class="col-12 col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id_kategori'] ?>" <?= $filter['kategori'] === (string) $category['id_kategori'] ? 'selected' : '' ?>>
                                    <?= esc($category['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($jenisLaporan !== 'saldo'): ?>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="<?= esc($filter['tanggal_mulai']) ?>">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="<?= esc($filter['tanggal_selesai']) ?>">
                        </div>
                    <?php endif; ?>
                    <div class="col-12 col-md">
                        <label class="form-label">Pencarian</label>
                        <input type="search" name="q" class="form-control form-control-sm" value="<?= esc($filter['q']) ?>" placeholder="Kode, nama barang, area...">
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2">
                        <button class="btn btn-success btn-sm fw-bold"><i class="fa-solid fa-search me-1"></i> FILTER</button>
                        <a href="<?= base_url('pendataan/laporan') . '?jenis_laporan=' . rawurlencode($jenisLaporan) ?>" class="btn btn-outline-secondary btn-sm" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($jenisLaporan === 'saldo'): ?>
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-4"><div class="report-stat"><div class="report-stat-label">Area Memiliki Saldo</div><div class="report-stat-value"><?= number_format($summary['area']) ?></div></div></div>
                <div class="col-6 col-md-4"><div class="report-stat"><div class="report-stat-label">Baris Barang</div><div class="report-stat-value"><?= number_format($summary['baris']) ?></div></div></div>
                <div class="col-12 col-md-4"><div class="report-stat"><div class="report-stat-label">Total Fisik</div><div class="report-stat-value"><?= esc($formatQty($summary['total_fisik'])) ?></div></div></div>
            </div>

            <div class="card report-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div><h2 class="h6 fw-bold mb-0">Saldo Aktual per Area</h2><div class="small text-secondary mt-1"><?= count($rows) ?> kombinasi Area dan Barang</div></div>
                    <span class="small text-secondary">Posisi data: <?= esc(date('d-m-Y')) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover report-table mb-0">
                            <thead><tr><th>No</th><th>Area</th><th>Barang</th><th class="text-end">Baik</th><th class="text-end">Rusak</th><th class="text-end">Dalam Perbaikan</th><th class="text-end">Tidak Layak</th><th class="text-end">Total Fisik</th></tr></thead>
                            <tbody>
                            <?php if (empty($rows)): ?>
                                <tr><td colspan="8"><div class="report-empty"><i class="fa-solid fa-box-open fa-2x mb-2 d-block opacity-50"></i>Belum ada saldo pada filter ini.</div></td></tr>
                            <?php else: ?>
                                <?php foreach ($rows as $i => $row): ?>
                                    <?php $total = (float) $row['stok_baik'] + (float) $row['stok_rusak'] + (float) $row['stok_perbaikan'] + (float) $row['stok_tidak_layak']; ?>
                                    <tr>
                                        <td class="text-center text-secondary"><?= $i + 1 ?></td>
                                        <td><div class="fw-bold"><?= esc($row['nama_area']) ?></div><span class="report-code"><?= esc($row['kode_area']) ?></span><div class="small text-secondary mt-1"><?= esc($fungsiLabels[$row['fungsi_stok']] ?? $row['fungsi_stok']) ?><?= $row['nama_factory'] ? ' · ' . esc($row['nama_factory']) : '' ?></div></td>
                                        <td><div class="fw-bold"><?= esc($row['nama_barang']) ?></div><span class="report-code"><?= esc($row['kode_barang']) ?></span><div class="small text-secondary mt-1"><?= esc($row['nama_kategori'] ?? '-') ?> · <?= esc($row['kode_satuan'] ?? '-') ?></div></td>
                                        <td class="text-end report-number fw-semibold text-success"><?= esc($formatQty($row['stok_baik'])) ?></td>
                                        <td class="text-end report-number"><?= esc($formatQty($row['stok_rusak'])) ?></td>
                                        <td class="text-end report-number"><?= esc($formatQty($row['stok_perbaikan'])) ?></td>
                                        <td class="text-end report-number"><?= esc($formatQty($row['stok_tidak_layak'])) ?></td>
                                        <td class="text-end report-number fw-bold"><?= esc($formatQty($total)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-4"><div class="report-stat"><div class="report-stat-label">Total Transaksi</div><div class="report-stat-value"><?= number_format($summary['transaksi']) ?></div></div></div>
                <div class="col-6 col-md-4"><div class="report-stat"><div class="report-stat-label">Baris Rincian</div><div class="report-stat-value"><?= number_format($summary['baris']) ?></div></div></div>
                <div class="col-12 col-md-4"><div class="report-stat"><div class="report-stat-label">Total Jumlah</div><div class="report-stat-value"><?= esc($formatQty($summary['total_qty'])) ?></div></div></div>
            </div>

            <div class="card report-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div><h2 class="h6 fw-bold mb-0"><?= $jenisLaporan === 'mutasi' ? 'Riwayat Mutasi Pindah Area' : 'Riwayat Barang Masuk/Keluar' ?></h2><div class="small text-secondary mt-1"><?= count($rows) ?> baris rincian</div></div>
                    <span class="small text-secondary">Hanya transaksi yang sudah memperbarui saldo</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover report-table mb-0">
                            <thead><tr><th>No</th><th>Tanggal</th><th>Nomor</th><th>Jenis</th><th>Barang</th><th class="text-end">Jumlah</th><th>Satuan</th><th>Area Asal</th><th>Area Tujuan</th><th>Kondisi</th><th>Dicatat Oleh</th></tr></thead>
                            <tbody>
                            <?php if (empty($rows)): ?>
                                <tr><td colspan="11"><div class="report-empty"><i class="fa-solid fa-file-circle-question fa-2x mb-2 d-block opacity-50"></i>Tidak ada transaksi pada filter ini.</div></td></tr>
                            <?php else: ?>
                                <?php foreach ($rows as $i => $row): ?>
                                    <?php
                                    $jenisText = ['SALDO_AWAL' => 'Saldo Awal', 'PENERIMAAN' => 'Barang Masuk', 'PENGELUARAN' => 'Barang Keluar', 'TRANSFER' => 'Pindah Area'];
                                    $condition = $row['kondisi_tujuan'] ?: $row['kondisi_asal'];
                                    $areaAsal = trim(($row['kode_area_asal'] ?? '') . ' ' . ($row['nama_area_asal'] ?? ''));
                                    $areaTujuan = trim(($row['kode_area_tujuan'] ?? '') . ' ' . ($row['nama_area_tujuan'] ?? ''));
                                    ?>
                                    <tr>
                                        <td class="text-center text-secondary"><?= $i + 1 ?></td>
                                        <td class="text-nowrap"><?= esc(date('d-m-Y H:i', strtotime($row['tanggal_transaksi']))) ?></td>
                                        <td class="fw-semibold text-nowrap"><?= esc($row['nomor_transaksi']) ?></td>
                                        <td><?= esc($jenisText[$row['jenis_transaksi']] ?? $row['jenis_transaksi']) ?></td>
                                        <td><div class="fw-semibold"><?= esc($row['nama_barang']) ?></div><span class="report-code"><?= esc($row['kode_barang']) ?></span><div class="small text-secondary mt-1"><?= esc($row['nama_kategori'] ?? '-') ?></div></td>
                                        <td class="text-end report-number fw-bold"><?= esc($formatQty($row['jumlah'])) ?></td>
                                        <td><?= esc($row['kode_satuan'] ?? '-') ?></td>
                                        <td><?= $areaAsal !== '' ? esc($areaAsal) : '-' ?></td>
                                        <td><?= $areaTujuan !== '' ? esc($areaTujuan) : '-' ?></td>
                                        <td><?= esc($condition ? ucwords(strtolower(str_replace('_', ' ', $condition))) : '-') ?></td>
                                        <td><?= esc($row['nama_pencatat'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
