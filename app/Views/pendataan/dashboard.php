<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .pendataan-intro {
        color: var(--bs-secondary-color);
        font-size: 0.92rem;
    }

    .pendataan-menu-card {
        height: 100%;
        border: 1px solid var(--bs-border-color);
        border-top: 3px solid var(--bs-success);
        border-radius: 10px;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .pendataan-menu-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .4rem 1rem rgba(0, 0, 0, .08) !important;
    }

    .pendataan-menu-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin-bottom: .9rem;
        border-radius: 11px;
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
        font-size: 1.15rem;
    }

    .pendataan-menu-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: .4rem;
        color: var(--bs-body-color);
    }

    .pendataan-menu-description {
        min-height: 44px;
        color: var(--bs-secondary-color);
        font-size: .84rem;
        line-height: 1.5;
    }

    .pendataan-status {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1 class="mb-1 fs-3">PENDATAAN BARANG</h1>
                <div class="pendataan-intro">
                    Selamat datang, <?= esc(session()->get('full_name')) ?>.
                    Kelola data barang, transaksi, saldo, dan laporan dalam satu halaman.
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <div class="row g-3">
            <?php
            $menu = [
                [
                    'Master Data',
                    'fa-database',
                    'Factory, area, kategori, satuan, dan barang.',
                    base_url('pendataan/master/factory'),
                ],
                [
                    'Transaksi',
                    'fa-right-left',
                    'Penerimaan, penempatan, transfer, dan persetujuan.',
                    null,
                ],
                [
                    'Saldo Barang',
                    'fa-warehouse',
                    'Jumlah barang berdasarkan area dan kondisi.',
                    null,
                ],
                [
                    'Laporan',
                    'fa-file-lines',
                    'Rekap stok dan riwayat transaksi.',
                    null,
                ],
            ];

            foreach ($menu as [$judul, $ikon, $deskripsi, $url]):
            ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card pendataan-menu-card shadow-sm mb-0">
                        <div class="card-body p-4">
                            <div class="pendataan-menu-icon">
                                <i class="fa-solid <?= esc($ikon) ?>"></i>
                            </div>
                            <h2 class="pendataan-menu-title"><?= esc($judul) ?></h2>
                            <p class="pendataan-menu-description mb-3"><?= esc($deskripsi) ?></p>
                            <?php if ($url): ?>
                                <a href="<?= esc($url, 'attr') ?>" class="btn btn-success btn-sm fw-bold">
                                    BUKA MASTER DATA <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            <?php else: ?>
                                <span class="badge rounded-pill text-bg-secondary pendataan-status">
                                    SEGERA HADIR
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>
