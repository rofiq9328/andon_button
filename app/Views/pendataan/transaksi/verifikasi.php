<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="text-secondary small text-uppercase fw-semibold">Pendataan Barang / Transaksi</div>
                <h1 class="mb-1 fs-3">VERIFIKASI TRANSAKSI</h1>
                <div class="text-secondary small">Daftar transaksi yang menunggu pemeriksaan Verifikator.</div>
            </div>
            <a href="<?= base_url('pendataan/transaksi') ?>" class="btn btn-outline-secondary btn-sm fw-semibold"><i class="fa-solid fa-arrow-left me-1"></i> KEMBALI</a>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h6 fw-bold mb-0">Menunggu Verifikasi</h2>
                <span class="badge text-bg-primary"><?= count($transaksi) ?> transaksi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="text-center">No</th><th>Nomor Transaksi</th><th>Tanggal</th><th>Jenis</th><th>Dibuat Oleh</th><th>Keterangan</th><th class="text-center">Aksi</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($transaksi)): ?>
                            <tr><td colspan="7" class="text-center text-secondary py-5"><i class="fa-solid fa-circle-check fa-2x mb-2 d-block"></i>Tidak ada transaksi yang menunggu verifikasi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($transaksi as $i => $row): ?>
                                <?php $jenis = ['SALDO_AWAL'=>'Saldo Awal','PENERIMAAN'=>'Barang Masuk','PENGELUARAN'=>'Barang Keluar','TRANSFER'=>'Pindah Area']; ?>
                                <tr>
                                    <td class="text-center text-secondary"><?= $i + 1 ?></td>
                                    <td class="fw-semibold text-nowrap"><?= esc($row['nomor_transaksi']) ?></td>
                                    <td class="text-nowrap"><?= esc(date('d-m-Y H:i', strtotime($row['tanggal_transaksi']))) ?></td>
                                    <td><?= esc($jenis[$row['jenis_transaksi']] ?? $row['jenis_transaksi']) ?></td>
                                    <td><?= esc($row['nama_pembuat'] ?: '-') ?></td>
                                    <td><?= esc($row['keterangan'] ?: '-') ?></td>
                                    <td class="text-center"><a href="<?= base_url('pendataan/transaksi/detail/' . (int) $row['id_transaksi']) ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i> Periksa</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
