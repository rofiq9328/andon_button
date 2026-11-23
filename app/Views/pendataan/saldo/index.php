<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .saldo-intro{color:var(--bs-secondary-color);font-size:.88rem}
    .saldo-card{border:1px solid var(--bs-border-color);border-top:3px solid var(--bs-success);border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
    .saldo-card .card-header{background:var(--bs-body-bg);border-bottom:1px solid var(--bs-border-color);padding:.85rem 1rem}
    .saldo-stat{height:100%;border:1px solid var(--bs-border-color);border-radius:9px;padding:1rem;background:var(--bs-body-bg)}
    .saldo-stat-label{font-size:.72rem;font-weight:700;color:var(--bs-secondary-color);text-transform:uppercase;letter-spacing:.04em}
    .saldo-stat-value{font-size:1.45rem;font-weight:800;line-height:1.2;margin-top:.3rem}
    .saldo-label{font-size:.7rem;color:var(--bs-secondary-color);font-weight:700;text-transform:uppercase;letter-spacing:.04em}
    .saldo-table th{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color);font-size:.68rem;font-weight:750;text-transform:uppercase;letter-spacing:.035em;white-space:nowrap;vertical-align:middle}
    .saldo-table td{vertical-align:middle;font-size:.82rem}
    .saldo-table .area-name,.saldo-table .barang-name{font-weight:700;color:var(--bs-body-color)}
    .saldo-code{display:inline-flex;padding:.25rem .45rem;border-radius:6px;background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis);font-size:.7rem;font-weight:700}
    .saldo-condition{display:block;color:var(--bs-secondary-color);font-size:.67rem;margin-top:.15rem}
    .saldo-filter .form-label{font-size:.76rem;font-weight:650;color:var(--bs-secondary-color);margin-bottom:.3rem}
    .saldo-note{padding:.8rem .95rem;border-radius:8px;background:var(--bs-info-bg-subtle);color:var(--bs-info-text-emphasis);font-size:.8rem}
    .saldo-empty{padding:3.5rem 1rem;text-align:center;color:var(--bs-secondary-color)}
    .saldo-number{font-variant-numeric:tabular-nums;white-space:nowrap}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$statusLabel = [
    'KURANG' => 'Kurang',
    'SESUAI' => 'Sesuai',
    'LEBIH' => 'Lebih',
    'TANPA_STANDAR' => 'Tanpa Standar',
];
$statusClass = [
    'KURANG' => 'text-bg-danger',
    'SESUAI' => 'text-bg-success',
    'LEBIH' => 'text-bg-primary',
    'TANPA_STANDAR' => 'text-bg-secondary',
];
$fungsiLabel = [
    'PENGGUNA' => 'Area Pengguna',
    'PENYIMPANAN' => 'Penyimpanan',
    'KEDUANYA' => 'Keduanya',
];
$formatQty = static function ($value) {
    return number_format((float) $value, 2, ',', '.');
};
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-8">
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Persediaan</div>
                <h1 class="mb-1 fs-3">SALDO BARANG</h1>
                <div class="saldo-intro">Pantau jumlah barang aktual pada setiap Area, termasuk rincian kondisi dan pemenuhan kebutuhan standar.</div>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="<?= base_url('pendataan/saldo?fungsi=PENYIMPANAN') ?>" class="btn btn-success btn-sm fw-bold">
                    <i class="fa-solid fa-warehouse me-1"></i> LIHAT PENYIMPANAN
                </a>
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
            <div class="col-6 col-md-4 col-xl-3">
                <div class="saldo-stat">
                    <div class="saldo-stat-label">Area Tercatat</div>
                    <div class="saldo-stat-value"><?= number_format($summary['area']) ?></div>
                    <div class="small text-secondary mt-1">Memiliki saldo atau standar aktif</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="saldo-stat">
                    <div class="saldo-stat-label">Baris Barang</div>
                    <div class="saldo-stat-value"><?= number_format($summary['baris_barang']) ?></div>
                    <div class="small text-secondary mt-1">Kombinasi Area dan Barang</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="saldo-stat">
                    <div class="saldo-stat-label">Kebutuhan Kurang</div>
                    <div class="saldo-stat-value text-danger"><?= number_format($summary['kurang']) ?></div>
                    <div class="small text-secondary mt-1">Saldo Baik di bawah standar</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="saldo-stat">
                    <div class="saldo-stat-label">Tanpa Standar</div>
                    <div class="saldo-stat-value"><?= number_format($summary['tanpa_standar']) ?></div>
                    <div class="small text-secondary mt-1">Saldo tidak dibandingkan kebutuhan</div>
                </div>
            </div>
        </div>

        <div class="saldo-note mb-3">
            <i class="fa-solid fa-circle-info me-1"></i>
            Perbandingan kebutuhan pada Area Pengguna/Keduanya menggunakan jumlah berkondisi <strong>Baik</strong>. Area Penyimpanan murni tidak dibandingkan dengan standar dan hanya menampilkan saldo aktual. Angka kekurangan bukan saldo fisik minus. Isi <strong>Saldo Awal</strong> oleh Administrator untuk mencatat stok awal yang sudah tersedia.
        </div>

        <div class="card saldo-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="h6 fw-bold mb-0">Rincian Saldo per Area</h2>
                    <div class="small text-secondary mt-1"><?= count($items) ?> baris ditampilkan</div>
                </div>
                <span class="small text-secondary">Posisi data: <?= esc(date('d-m-Y')) ?></span>
            </div>

            <div class="card-body border-bottom">
                <form method="get" action="<?= base_url('pendataan/saldo') ?>" class="row g-2 align-items-end saldo-filter">
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
                    <div class="col-6 col-md-2">
                        <label class="form-label">Fungsi Stok</label>
                        <select name="fungsi" class="form-select form-select-sm">
                            <option value="">Semua Fungsi</option>
                            <option value="PENGGUNA" <?= $filter['fungsi'] === 'PENGGUNA' ? 'selected' : '' ?>>Area Pengguna</option>
                            <option value="PENYIMPANAN" <?= $filter['fungsi'] === 'PENYIMPANAN' ? 'selected' : '' ?>>Penyimpanan</option>
                            <option value="KEDUANYA" <?= $filter['fungsi'] === 'KEDUANYA' ? 'selected' : '' ?>>Keduanya</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
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
                    <div class="col-6 col-md-2">
                        <label class="form-label">Status Kebutuhan</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="KURANG" <?= $filter['status'] === 'KURANG' ? 'selected' : '' ?>>Kurang</option>
                            <option value="SESUAI" <?= $filter['status'] === 'SESUAI' ? 'selected' : '' ?>>Sesuai</option>
                            <option value="LEBIH" <?= $filter['status'] === 'LEBIH' ? 'selected' : '' ?>>Lebih</option>
                            <option value="TANPA_STANDAR" <?= $filter['status'] === 'TANPA_STANDAR' ? 'selected' : '' ?>>Tanpa Standar</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Pencarian</label>
                        <input type="search" name="q" class="form-control form-control-sm" value="<?= esc($filter['q']) ?>" placeholder="Area atau barang...">
                    </div>
                    <div class="col-12 col-md-1 d-flex gap-2">
                        <button class="btn btn-success btn-sm flex-grow-1" title="Terapkan filter"><i class="fa-solid fa-search"></i></button>
                        <a href="<?= base_url('pendataan/saldo') ?>" class="btn btn-outline-secondary btn-sm" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover saldo-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:50px">No</th>
                                <th>Area</th>
                                <th>Barang</th>
                                <th class="text-end">Standar</th>
                                <th class="text-end">Baik</th>
                                <th class="text-end">Rusak</th>
                                <th class="text-end">Dalam Perbaikan</th>
                                <th class="text-end">Tidak Layak</th>
                                <th class="text-end">Total Fisik</th>
                                <th class="text-end">Selisih</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="11">
                                    <div class="saldo-empty">
                                        <i class="fa-solid fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                        <div class="fw-semibold">Belum ada saldo atau standar pada filter ini.</div>
                                        <div class="small mt-1">Saldo akan tampil setelah ada saldo awal atau transaksi yang sudah diposting.</div>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $i => $item): ?>
                                <?php
                                $fungsi = $fungsiLabel[$item['fungsi_stok']] ?? $item['fungsi_stok'];
                                $standardText = $item['jumlah_standar'] === null ? '-' : $formatQty($item['jumlah_standar']);
                                $selisihText = $item['selisih'] === null
                                    ? '-'
                                    : ($item['selisih'] < -0.00001
                                        ? 'Kurang ' . $formatQty(abs($item['selisih']))
                                        : ($item['selisih'] > 0.00001
                                            ? 'Lebih ' . $formatQty($item['selisih'])
                                            : 'Sesuai'));
                                ?>
                                <tr>
                                    <td class="text-center text-secondary"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="area-name"><?= esc($item['nama_area']) ?></div>
                                        <span class="saldo-code"><?= esc($item['kode_area']) ?></span>
                                        <span class="saldo-condition"><?= esc($fungsi) ?><?= !empty($item['nama_factory']) ? ' · ' . esc($item['nama_factory']) : '' ?></span>
                                        <?php if ((int) $item['status_area'] !== 1): ?><span class="badge text-bg-secondary mt-1">Area Nonaktif</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="barang-name"><?= esc($item['nama_barang']) ?></div>
                                        <span class="saldo-code"><?= esc($item['kode_barang']) ?></span>
                                        <span class="saldo-condition"><?= esc($item['nama_kategori'] ?? '-') ?> · <?= esc($item['kode_satuan'] ?? '-') ?></span>
                                        <?php if ((int) $item['status_barang'] !== 1): ?><span class="badge text-bg-secondary mt-1">Barang Nonaktif</span><?php endif; ?>
                                    </td>
                                    <td class="text-end saldo-number fw-semibold">
                                        <?= esc($standardText) ?>
                                        <?php if ($item['jumlah_standar'] !== null): ?><span class="saldo-condition"><?= esc($item['kode_satuan'] ?? '') ?></span><?php endif; ?>
                                    </td>
                                    <td class="text-end saldo-number fw-bold text-success"><?= esc($formatQty($item['stok_baik'])) ?></td>
                                    <td class="text-end saldo-number"><?= esc($formatQty($item['stok_rusak'])) ?></td>
                                    <td class="text-end saldo-number"><?= esc($formatQty($item['stok_perbaikan'])) ?></td>
                                    <td class="text-end saldo-number"><?= esc($formatQty($item['stok_tidak_layak'])) ?></td>
                                    <td class="text-end saldo-number fw-semibold"><?= esc($formatQty($item['total_fisik'])) ?></td>
                                    <td class="text-end saldo-number fw-semibold <?= $item['selisih'] !== null && $item['selisih'] < 0 ? 'text-danger' : ($item['selisih'] > 0 ? 'text-primary' : '') ?>">
                                        <?= esc($selisihText) ?>
                                    </td>
                                    <td><span class="badge <?= esc($statusClass[$item['status_kebutuhan']] ?? 'text-bg-secondary') ?>"><?= esc($statusLabel[$item['status_kebutuhan']] ?? $item['status_kebutuhan']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white small text-secondary">
                <i class="fa-solid fa-circle-info me-1"></i>
                Total Fisik merupakan penjumlahan semua kondisi. Kolom Selisih menampilkan jumlah kekurangan atau kelebihan terhadap standar, bukan saldo fisik negatif.
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
