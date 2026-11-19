<?= $this->extend('pendataan/layout') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Dashboard Pendataan Barang</h1>
    <div class="text-secondary">Selamat datang, <?= esc(session()->get('pb_user')['nama_user']) ?>.</div>
</div>

<div class="row g-3">
    <?php
    $menu = [
        ['Master Data', 'fa-database', 'Factory, area, kategori, satuan, dan barang.'],
        ['Transaksi', 'fa-right-left', 'Penerimaan, penempatan, transfer, dan persetujuan.'],
        ['Saldo Barang', 'fa-warehouse', 'Jumlah barang per area dan kondisi.'],
        ['Laporan', 'fa-file-lines', 'Rekap stok dan riwayat transaksi.'],
    ];
    foreach ($menu as [$judul, $ikon, $deskripsi]):
    ?>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <i class="fa-solid <?= $ikon ?> fa-lg text-success mb-2"></i>
                    <h2 class="h6 fw-bold mb-1"><?= esc($judul) ?></h2>
                    <div class="small text-secondary mb-3"><?= esc($deskripsi) ?></div>
                    <span class="badge text-bg-secondary">Segera hadir</span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
