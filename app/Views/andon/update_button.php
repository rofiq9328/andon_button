<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    /* Ganti aksen warna Tabulator dari biru default (#0d6efd) ke success/hijau,
       biar senada dengan card-outline card-success & tombol Upload Excel */

    /* Tombol nomor halaman yang sedang aktif di pagination */
    #tableButton .tabulator-footer .tabulator-page.active {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
    }

    /* Warna teks saat hover tombol pagination (Awal/Maju/Mundur/Akhir/nomor) */
    #tableButton .tabulator-footer .tabulator-page:not(.disabled):hover {
        color: var(--bs-success);
    }

    /* Panah sort saat kolom sedang diurutkan */
    #tableButton .tabulator-header .tabulator-col.tabulator-sortable[aria-sort="ascending"] .tabulator-col-content .tabulator-col-sorter,
    #tableButton .tabulator-header .tabulator-col.tabulator-sortable[aria-sort="descending"] .tabulator-col-content .tabulator-col-sorter {
        color: var(--bs-success);
    }

    #tableButton .tabulator-header .tabulator-col.tabulator-sortable[aria-sort="ascending"] .tabulator-col-content .tabulator-col-sorter .tabulator-arrow {
        border-bottom-color: var(--bs-success);
    }

    #tableButton .tabulator-header .tabulator-col.tabulator-sortable[aria-sort="descending"] .tabulator-col-content .tabulator-col-sorter .tabulator-arrow {
        border-top-color: var(--bs-success);
    }

    /* Fokus di kotak pencarian */
    #searchButton:focus {
        border-color: var(--bs-success);
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    /* Baris data selalu putih (tidak zebra/selang-seling abu-abu) */
    #tableButton .tabulator-row,
    #tableButton .tabulator-row.tabulator-row-even {
        background-color: #fff;
    }

    /* Dropdown "Tampilkan X data" tetap terang, tidak ikut dark mode OS/browser
       (halaman ini punya meta color-scheme: light dark, jadi elemen native
       seperti <select> bisa otomatis jadi gelap kalau OS/browser mode gelap) */
    #tableButton .tabulator-page-size {
        background-color: #fff;
        color: #212529;
        color-scheme: light;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="app-content-header mb-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-3">
                    <h1 class="mb-0 fs-3">DATA BUTTON ID</h1>
                    <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUploadExcel" title="Upload Excel">
                        <i class="fas fa-file-excel me-1"></i>
                        <span class="d-none d-sm-inline">Upload Excel</span>
                    </button>
                    <button type="button" class="btn btn-success btn-sm shadow-sm ms-1" onclick="clearAllButton()" title="Kosongkan semua data">
                        <i class="fas fa-trash me-1"></i> Kosongkan Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Card Data Table -->
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header bg-white d-flex justify-content-end">
                <div class="input-group input-group-sm" style="max-width: 260px;">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchButton" class="form-control" placeholder="Cari Data...">
                </div>
            </div>

            <div class="card-body">
                <!-- Tabulator akan membangun tabel di dalam div ini -->
                <div id="tableButton"></div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================= -->
<!-- MODAL UPLOAD EXCEL -->
<!-- ========================================================= -->
<div class="modal fade" id="modalUploadExcel" tabindex="-1" aria-labelledby="modalUploadExcelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalUploadExcelLabel"><i class="fas fa-file-excel me-1"></i> Import Excel Data ID Button</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('buttoncontroller/processExcel') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="file_excel" class="font-weight-normal fw-bold">Pilih File Excel</label>
                        <input type="file" class="form-control" id="file_excel" name="file_excel" accept=".xls,.xlsx" required>
                    </div>
                    <div class="template-download text-muted mt-3" style="font-size: 0.95rem;">
                        <i class="fas fa-info-circle"></i> Template Upload :
                        <a href="<?= base_url('assets/templates/button_id.xlsx') ?>" download class="text-success text-decoration-none fw-bold">
                            <i class="far fa-file-excel"></i> button_id.xlsx
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i></i> Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL EDIT DATA -->
<!-- ========================================================= -->
<div class="modal fade" id="modalEditButton" tabindex="-1" aria-labelledby="modalEditButtonLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalEditButtonLabel"><i class="fas fa-edit me-1"></i> Edit Data ID Button</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('buttoncontroller/updateSingle') ?>" method="post">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="fw-bold">ID Button (Tidak bisa diubah)</label>
                        <input type="text" class="form-control" id="edit_id" name="id" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Factory</label>
                        <input type="text" class="form-control" id="edit_factory" name="factory" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Line</label>
                        <input type="text" class="form-control" id="edit_line" name="line" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Worker</label>
                        <input type="text" class="form-control" id="edit_worker" name="worker" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- ========================================================= -->
<!-- SCRIPT JS -->
<!-- ========================================================= -->
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') ?>"></script>

<script>
    const buttonData = <?= json_encode($buttons ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    const flashSuccess = <?= json_encode(session()->getFlashdata('success'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const flashError = <?= json_encode(session()->getFlashdata('error'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const flashWarning = <?= json_encode(session()->getFlashdata('warning'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const flashWarningDetail = <?= json_encode(session()->getFlashdata('warningDetail') ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    document.addEventListener("DOMContentLoaded", function() {

        // --- INISIALISASI BOOTSTRAP TOAST ---
        const toastEl = document.getElementById('liveToast');
        // Toast otomatis hilang dalam 5 detik (5000ms)
        const bsToast = new bootstrap.Toast(toastEl, {
            delay: 5000
        });

        const toastHeader = document.getElementById('toastHeader');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');
        const toastCloseBtn = document.getElementById('toastCloseBtn');

        function showBootstrapToast(type, title, message) {
            // Reset class ke default
            toastHeader.className = 'toast-header border-0';
            toastBody.className = 'toast-body bg-white text-dark'; // Default body putih, teks gelap
            toastCloseBtn.className = 'btn-close';

            if (type === 'success') {
                toastHeader.classList.add('bg-success', 'text-white');
                toastIcon.className = 'fas fa-check-circle me-2 text-white';
                toastCloseBtn.classList.add('btn-close-white');
            } else if (type === 'error') {
                toastHeader.classList.add('bg-danger', 'text-white');
                toastIcon.className = 'fas fa-times-circle me-2 text-white';
                toastCloseBtn.classList.add('btn-close-white');
            } else if (type === 'warning') {
                // Style peringatan / warning
                toastHeader.classList.add('bg-warning', 'text-dark');
                toastIcon.className = 'far fa-circle me-2 text-dark';
            }

            toastTitle.innerText = title;
            toastBody.innerHTML = message;
            bsToast.show();
        }

        // Tampilkan toast jika ada flashdata
        if (flashSuccess) {
            showBootstrapToast('success', 'Berhasil', flashSuccess);
        }
        if (flashError) {
            showBootstrapToast('error', 'Gagal', flashError);
        }
        if (flashWarning) {
            let warningHtml = `<div>${flashWarning}</div>`;

            if (Array.isArray(flashWarningDetail) && flashWarningDetail.length) {
                const shown = flashWarningDetail.slice(0, 5);
                const extra = flashWarningDetail.length - shown.length;

                warningHtml += `<ul class="mb-0 mt-2 ps-3 small" style="max-height:150px; overflow-y:auto;">
                    ${shown.map(item => `<li>${item}</li>`).join('')}
                </ul>`;

                if (extra > 0) {
                    warningHtml += `<small class="text-muted d-block mt-1">+${extra} konflik lainnya</small>`;
                }
            }

            showBootstrapToast('warning', 'Perhatian', warningHtml);
        }
        // --- INISIALISASI BS-CUSTOM-FILE-INPUT ---
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }

        // --- INISIALISASI TABULATOR ---
        if (typeof Tabulator === 'undefined') {
            console.error('Tabulator belum diload di layout/main Anda!');
            return;
        }

        const table = new Tabulator("#tableButton", {
            data: buttonData,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "Tidak ada data",
            pagination: true,
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100, true],
            paginationCounter: function(pageSize, currentRow, currentPage, totalRows) {
                const start = totalRows === 0 ? 0 : currentRow;
                const end = Math.min(currentRow + pageSize - 1, totalRows);
                return `Menampilkan ${start} sampai ${end} dari ${totalRows} data`;
            },
            locale: "id-id",
            langs: {
                "id-id": {
                    "pagination": {
                        "page_size": "",
                        "first": "first",
                        "first_title": "Halaman Awal",
                        "last": "last",
                        "last_title": "Halaman Akhir",
                        "prev": "prev",
                        "prev_title": "Halaman Sebelumnya",
                        "next": "next",
                        "next_title": "Halaman Berikutnya",
                        "all": "All"
                    },
                    "data": {
                        "loading": "Memuat...",
                        "error": "Gagal memuat data"
                    }
                }
            },
            columns: [{
                    title: "No",
                    formatter: "rownum",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 60,
                    headerSort: false
                },
                {
                    title: "ID Button",
                    field: "id",
                    minWidth: 160
                },
                {
                    title: "Factory",
                    field: "factory",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 110
                },
                {
                    title: "Line",
                    field: "line",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 100
                },
                {
                    title: "Worker",
                    field: "worker",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 110
                },
                {
                    title: "Aksi",
                    field: "aksi",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    headerSort: false,
                    width: 110,
                    formatter: function() {
                        return `
                        <button class="btn btn-sm btn-warning btn-edit-row" title="Edit Data">
                            <i class="fas fa-edit text-white"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete-row" title="Hapus Data">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    },
                    cellClick: function(e, cell) {
                        const rowData = cell.getRow().getData();
                        if (e.target.closest('.btn-edit-row')) {
                            editButton(rowData.id, rowData.factory, rowData.line, rowData.worker);
                        } else if (e.target.closest('.btn-delete-row')) {
                            deleteButton(rowData.id);
                        }
                    }
                }
            ],
        });

        // Search global manual
        const searchInput = document.getElementById('searchButton');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const q = this.value.trim().toLowerCase();
                if (!q) {
                    table.clearFilter();
                    return;
                }
                table.setFilter(function(data) {
                    return (
                        String(data.id ?? '').toLowerCase().includes(q) ||
                        String(data.factory ?? '').toLowerCase().includes(q) ||
                        String(data.line ?? '').toLowerCase().includes(q) ||
                        String(data.worker ?? '').toLowerCase().includes(q)
                    );
                });
            });
        }
    });

    // Fungsi Membuka Modal Edit & Isi Data
    window.editButton = function(id, factory, line, worker) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_factory').value = factory;
        document.getElementById('edit_line').value = line;
        document.getElementById('edit_worker').value = worker;

        var modalElement = document.getElementById('modalEditButton');
        var editModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        editModal.show();
    };

    // Fungsi Hapus Data via SweetAlert2 (Konfirmasi hapus tetap pakai popup SweetAlert karena lebih aman)
    window.deleteButton = function(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Data?',
                text: `Anda yakin ingin menghapus data dengan ID ${id}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= base_url('buttoncontroller/delete/') ?>${id}`;
                }
            });
        } else {
            if (confirm(`Anda yakin ingin menghapus data dengan ID ${id}?`)) {
                window.location.href = `<?= base_url('buttoncontroller/delete/') ?>${id}`;
            }
        }
    };

    // Kosongkan semua data button, konfirmasi lewat SweetAlert2 (sama seperti hapus data)
    window.clearAllButton = function() {
        const kirim = function() {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = '<?= base_url('buttoncontroller/clearAll') ?>';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '<?= csrf_token() ?>';
            token.value = '<?= csrf_hash() ?>';
            form.appendChild(token);

            document.body.appendChild(form);
            form.submit();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Kosongkan Semua Data?',
                text: 'Semua data button akan dihapus. Setelah itu upload file Excel baru untuk mengisi ulang.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'DELETE',
                cancelButtonText: 'CANCEL'
            }).then((result) => {
                if (result.isConfirmed) {
                    kirim();
                }
            });
        } else if (confirm('Semua data button akan dihapus. Lanjutkan?')) {
            kirim();
        }
    };
</script>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div id="toastHeader" class="toast-header border-0">
            <i id="toastIcon" class="me-2"></i>
            <strong class="me-auto" id="toastTitle">Sistem</strong>
            <small>Baru saja</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close" id="toastCloseBtn"></button>
        </div>
        <div id="toastBody" class="toast-body">
            <!-- Pesan dinamis akan dimuat di sini via JS -->
        </div>
    </div>
</div>
<?= $this->endSection() ?>