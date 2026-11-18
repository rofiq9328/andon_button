<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    /* AdminLTE Card Styling khusus Andon */
    .andon-card {
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .andon-card:hover {
        transform: translateY(-3px);
    }

    /* LED status dot — warna, opacity, dan border/outline card semua sudah
       pakai utility & modifier bawaan AdminLTE/Bootstrap (bg-*, text-*,
       card-outline, card-{color}, opacity-25). Cuma efek glow ini yang
       custom, karena Bootstrap belum punya utility box-shadow. */
    .led-dot {
        transition: box-shadow 0.3s, opacity 0.3s;
    }

    .led-dot.is-active {
        box-shadow: 0 0 10px currentColor;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-3">
                    <h1 class="mb-0 fs-3">DASHBOAR ANDON</h1>
                    <button class="btn btn-outline-danger btn-sm fw-bold shadow-sm" onclick="clearAllLines()" title="Clear All Trouble">
                        <i class="fa-solid fa-power-off me-1"></i>
                        <span class="d-none d-sm-inline">CLEAR ALL</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Card Container Utama (AdminLTE 4 Style) -->
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title fw-bold text-dark">
                            
                        </h3>
                    </div>

                    <!-- Card Body (Konten Utama) -->
                    <div class="card-body">
                        <div id="line-container" class="row">
                        </div>
                    </div>
                </div>
                <!--end::Card-->
            </div>
        </div>
    </div>
</div>
<!--end::App Content-->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/script.js') ?>"></script>
<?= $this->endSection() ?>