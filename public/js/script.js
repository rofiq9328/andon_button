const CONFIG = {
    API: {
        STATUS: BASE_URL + 'api/andon/status',
        CLEAR: BASE_URL + 'api/andon/clear'
    },
    REFRESH_INTERVAL: 10000 // 10 detik
};

const LED_COLOR = { spv: 'primary', mk: 'warning', qc: 'success', mt: 'danger' };

const HEADER_COLOR = 'text-bg-success opacity-30 pe-none';

let currentData = [];
let statusRequestInProgress = false;

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('line-container');
    if (container) {
        container.innerHTML = `
            <div class="col-12 text-center py-5" role="status" aria-live="polite">
                <div class="spinner-border text-success mb-3" role="status" aria-hidden="true"></div>
                <h5 class="text-success fw-bold">MEMUAT STATUS ANDON</h5>
                <p class="text-muted mb-0">Menghubungkan perangkat line...</p>
            </div>`;
    }

    fetchLineStatus();

    // Auto Refresh setiap 10 detik
    setInterval(() => {
        fetchLineStatus();
    }, CONFIG.REFRESH_INTERVAL);
});

async function fetchLineStatus() {
    // Jangan menumpuk request jika pemeriksaan 77 perangkat sebelumnya belum selesai.
    if (statusRequestInProgress) return;
    statusRequestInProgress = true;

    try {
        const response = await fetch(CONFIG.API.STATUS, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const result = await response.json();
        if (result.success && Array.isArray(result.data)) {
            currentData = result.data;
            renderDashboard(currentData);
        }
    } catch (error) {
        console.error("Gagal mengambil data Andon:", error);
        const container = document.getElementById('line-container');
        if (container && currentData.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-wifi-off text-warning mb-3" style="font-size: 2.5rem;"></i>
                    <h5 class="fw-bold">STATUS ANDON BELUM TERSEDIA</h5>
                    <p class="text-muted mb-0">Koneksi sedang diperiksa kembali secara otomatis.</p>
                </div>`;
        }
    } finally {
        statusRequestInProgress = false;
    }
}

function getSeverity(lights) {
    if (lights.mt) return 'danger';
    if (lights.mk) return 'warning';
    if (lights.spv) return 'primary';
    if (lights.qc) return 'success';
    return 'secondary';
}

function renderDashboard(data) {
    const container = document.getElementById('line-container');
    container.innerHTML = '';

    // FILTER OTOMATIS: HANYA MENGAMBIL LINE YANG SEDANG TROUBLE
    const filteredData = data.filter(line => line.has_trouble === true);

    if (filteredData.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-shield-check text-success mb-3" style="font-size: 4rem;"></i>
                <h4 class="text-success fw-bold">SEMUA LINE AMAN</h4>
                <p class="text-muted">Tidak ada andon yang hidup</p>
            </div>`;
        return;
    }

    // Render Kartu Trouble
    filteredData.forEach(line => {
        const severity = getSeverity(line.lights); 
        const cardStateClass = `card-outline card-${severity}`;

        const renderWorkers = (type, workerArray) => {
            if (!workerArray || workerArray.length === 0) return '';
            return workerArray.map(w => {
                const cleanW = String(w).replace(/['"]+/g, '');
                const factory = line.factory || '1';

                return `
                    <div class="btn-group me-1 mb-1">
                        <span class="btn btn-sm btn-outline-danger rounded-start-pill pe-none">
                            <i class="bi bi-person-gear"></i> ${type} ${cleanW}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-end-pill"
                                onclick="window.clearSingle('${line.line_number}', '${cleanW}', '${type}', '${factory}')"
                                title="Clear ${type}">
                            <i class="bi bi-power"></i>
                        </button>
                    </div>
                `;
            }).join('');
        };

        const renderLed = (type) => {
            const active = !!line.lights[type];
            const color = LED_COLOR[type];
            const dotClass = active ? `bg-${color} text-${color} is-active` : 'bg-secondary text-secondary';

            return `
                <div class="text-center ${active ? '' : 'opacity-25'}">
                    <span class="d-inline-block rounded-circle led-dot ${dotClass}" style="width:22px;height:22px"></span>
                    <div class="small fw-bold text-secondary mt-1">${type.toUpperCase()}</div>
                </div>
            `;
        };

        const spvWorkers = renderWorkers('SPV', line.workers.spv);
        const mkWorkers = renderWorkers('MK', line.workers.mk);
        const qcWorkers = renderWorkers('QC', line.workers.qc);
        const mtWorkers = renderWorkers('MT', line.workers.mt);

        const html = `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                 <div class="card h-100 andon-card  shadow-sm">
                    <div class="card-header ${HEADER_COLOR} border-bottom-0">
                        <h3 class="card-title fw-bold fs-4 mb-0">LINE ${line.line_number}</h3>
                    </div>

                    <div class="card-body py-3">
                        <div class="d-flex justify-content-around p-3 mb-3 bg-body-tertiary border rounded">
                            ${renderLed('spv')}${renderLed('mk')}${renderLed('qc')}${renderLed('mt')}
                        </div>

                        <div class="trouble-list text-center" style="min-height: 50px;">
                            ${spvWorkers} ${mkWorkers} ${qcWorkers} ${mtWorkers}
                        </div>
                    </div>

                    <div class="card-footer bg-body border-top text-center text-muted py-2" style="font-size: 0.75rem;">
                        <i class="bi bi-diagram-3 me-1"></i> IP: ${line.ip}
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    });
}

// Handler Styling untuk Tombol Filter Group di mode Terang
window.updateFilterButtons = function(clickedBtn) {
    const buttons = document.querySelectorAll('#filter-group .btn');
    buttons.forEach(btn => {
        if (btn.innerText.includes('Trouble')) {
            btn.className = 'btn btn-outline-danger fw-bold';
        } else {
            btn.className = 'btn btn-outline-secondary fw-bold';
        }
    });

    if (clickedBtn.innerText.includes('Trouble')) {
        clickedBtn.className = 'btn btn-danger fw-bold active';
    } else {
        clickedBtn.className = 'btn btn-primary fw-bold active';
    }
};

window.filterBuilding = function(building) {
    currentFilter = building;
    updateFilterButtons(event.currentTarget);
    renderDashboard(currentData);
};

window.filterTroubleOnly = function() {
    currentFilter = 'TROUBLE';
    updateFilterButtons(event.currentTarget);
    renderDashboard(currentData);
};

window.searchLine = function() {
    renderDashboard(currentData);
};

// Fungsi Kirim Perintah Clear Satuan
window.clearSingle = async function(lineNumber, workerNumber, troubleType, factory) {
    const cleanWorker = String(workerNumber).replace(/['"]+/g, '');

    // Konfirmasi menggunakan SweetAlert2
    const result = await Swal.fire({
        title: 'Konfirmasi Clear',
        text: `Clear Andon untuk Line ${lineNumber}, ${troubleType} No. ${cleanWorker}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545', 
        cancelButtonColor: '#6c757d',  
        confirmButtonText: 'Clear',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(CONFIG.API.CLEAR, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    line_number: lineNumber,
                    worker_number: cleanWorker,
                    trouble_type: troubleType,
                    factory: factory
                })
            });

            const apiResult = await response.json();

            if (apiResult.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'dikirim',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                fetchLineStatus();
            } else {
                Swal.fire('Gagal!', apiResult.error || 'Respon error dari server', 'error');
            }
        } catch (error) {
            console.error("Terjadi error jaringan/JS:", error);
            Swal.fire('Error!', 'Terjadi kesalahan jaringan saat mengirim perintah clear.', 'error');
        }
    }
};

// Fungsi Clear Semua Line
window.clearAllLines = async function() {
    // Saring line yang memiliki status trouble dari data aktif saat ini
    const troubleLines = currentData.filter(line => line.has_trouble);

    if (troubleLines.length === 0) {
        Swal.fire({
            title: 'Informasi',
            text: 'tidak ada andon yang hidup',
            icon: 'info',
            confirmButtonColor: '#0d6efd' // Primary BS5
        });
        return;
    }

    // Konfirmasi menggunakan SweetAlert2
    const confirmResult = await Swal.fire({
        title: 'Clear Semua Line?',
        text: `mengirim sinyal clear ke semua line, Lanjutkan?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Clear',
        cancelButtonText: 'Batal',
        reverseButtons: true
    });

    if (confirmResult.isConfirmed) {
        // Tampilkan indikator loading
        Swal.fire({
            title: 'Memproses...',
            text: 'Mengirim sinyal clear all ke server...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
        for (const line of troubleLines) {
            const factory = line.factory || '1';
            const troubleTypes = ['SPV', 'MK', 'QC', 'MT'];

            for (const type of troubleTypes) {
                const workers = line.workers[type.toLowerCase()] || [];
                for (const worker of workers) {
                    const cleanWorker = String(worker).replace(/['"]+/g, '');
                    await fetch(CONFIG.API.CLEAR, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            line_number: line.line_number,
                            worker_number: cleanWorker,
                            trouble_type: type,
                            factory: factory
                        })
                    });
                }
            }
        }

        Swal.fire({
            title: 'Berhasil!',
            text: 'Semua sinyal clear berhasil dikirim.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });

        fetchLineStatus();
        } catch (error) {
        console.error("Terjadi error saat clear all:", error);
        Swal.fire('Error!', 'Terjadi kesalahan jaringan saat memproses perintah clear all.', 'error');
        }
    }
};