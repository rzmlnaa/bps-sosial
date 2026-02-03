@extends('layouts.admin')

@section('title', 'Input Data Seruti')

@section('content')
    <div class="fade-in-up">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Data Seruti</h2>
                <p class="text-muted mb-0">Kelola master data coicop dan input data konsumsi</p>
            </div>
            <a href="{{ route('seruti.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <!-- Tabs/Navigation -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white p-2">
                <ul class="nav nav-pills card-header-pills" id="serutiTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="master-tab" data-bs-toggle="tab" data-bs-target="#master"
                            type="button" role="tab" aria-controls="master" aria-selected="false"
                            style="border-radius: 8px;">
                            <i class="fas fa-tags me-2"></i>Master Kelompok
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="input-tab" data-bs-toggle="tab" data-bs-target="#input"
                            type="button" role="tab" aria-controls="input" aria-selected="true" style="border-radius: 8px;">
                            <i class="fas fa-edit me-2"></i>Input Data
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="serutiTabContent">

            <!-- Tab 1: Master Kelompok -->
            <div class="tab-pane fade" id="master" role="tabpanel" aria-labelledby="master-tab">
                <div class="row g-4">
                    <!-- Left: Paste Area -->
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3">Copy-Paste dari Excel</h5>
                                <p class="text-muted small">
                                    Salin data dari Excel dengan kolom (Kode | COICOP | SERUTI) lalu tempel di bawah ini.
                                </p>
                                <div class="form-group mb-3">
                                    <textarea id="pasteArea" class="form-control" rows="8"
                                        placeholder="Paste data Excel di sini..."
                                        style="font-family: monospace; font-size: 0.9rem;"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-secondary" id="clearBtn">Clear</button>
                                    <button class="btn btn-primary" id="previewBtn">Preview Data</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview & Existing Data -->
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Data Kelompok COICOP</h5>
                                    <button class="btn btn-success d-none" id="saveBtn">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="coicopTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4" style="width: 100px;">Kode</th>
                                            <th>Nama COICOP</th>
                                            <th>SERUTI</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coicopTableBody">
                                        @forelse($coicops as $c)
                                            <tr>
                                                <td class="ps-4 fw-bold">{{ $c->kode }}</td>
                                                <td>{{ $c->nama }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $c->seruti }}</span></td>
                                                <td class="text-center"><span class="badge bg-secondary">Tersimpan</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data. Silakan
                                                    input di atas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Input Data -->
            <div class="tab-pane fade show active" id="input" role="tabpanel" aria-labelledby="input-tab">
                <div class="row g-4">

                    <!-- LEFT COLUMN: FILTER -->
                    <div class="col-lg-4 col-xl-3">
                        <div class="card border-0 shadow-sm sticky-top"
                            style="top: 20px; z-index: 100; border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-uppercase text-secondary" style="letter-spacing: 0.5px;">1.
                                    Filter Data</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Tahun</label>
                                    <input type="number" id="inputYear" class="form-control" placeholder="YYYY"
                                        value="{{ date('Y') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Triwulan</label>
                                    <select id="inputQuarter" class="form-select">
                                        <option value="" selected disabled>Pilih Triwulan</option>
                                        <option value="1">Triwulan 1</option>
                                        <option value="2">Triwulan 2</option>
                                        <option value="3">Triwulan 3</option>
                                        <option value="4">Triwulan 4</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Kabupaten/Kota</label>
                                    <select id="inputKabupaten" class="form-select" size="10" style="height: 250px;">
                                        @foreach($kabupatens as $kab)
                                            <option value="{{ $kab->id }}">[{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted fst-italic mt-1 d-block">Pilih salah satu wilayah.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: CONTENT -->
                    <div class="col-lg-8 col-xl-9">
                        <div class="row g-4">
                            <!-- PASTE AREA -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-header bg-white py-3 border-bottom-0">
                                        <h6 class="fw-bold mb-0 text-uppercase text-secondary"
                                            style="letter-spacing: 0.5px;">2. Paste Data</h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <p class="text-muted small mb-2">
                                            Salin kolom nilai (tanpa header) dari Excel dan tempel di sini.
                                            Pastikan urutan baris sesuai dengan Master Kelompok (disortir berdasarkan Kode).
                                        </p>
                                        <div class="d-flex gap-3">
                                            <div class="flex-grow-1">
                                                <textarea id="pasteConsumption" class="form-control" rows="5"
                                                    placeholder="Paste nilai data di sini..."
                                                    style="font-family: monospace; font-size: 0.9rem; white-space: pre;"></textarea>
                                            </div>
                                            <div class="d-flex flex-column gap-2 justify-content-start">
                                                <button class="btn btn-primary" id="previewConsumptionBtn">
                                                    <i class="fas fa-sync-alt me-1"></i> Preview
                                                </button>
                                                <button class="btn btn-outline-secondary" id="clearConsumptionBtn">
                                                    <i class="fas fa-trash me-1"></i> Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PREVIEW / EXISTING DATA TABLE -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div
                                        class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0" id="tableHeaderTitle">Data Tersimpan - [Pilih Wilayah]</h5>
                                        <button class="btn btn-success d-none" id="saveConsumptionBtn">
                                            <i class="fas fa-save me-2"></i>Simpan Data
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle mb-0"
                                            style="font-size: 0.9rem;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-4" style="width: 80px;">Kode</th>
                                                    <th>COICOP / Komoditas</th>
                                                    <th>SERUTI</th>
                                                    <th class="text-end" style="width: 180px;">Konsumsi per Kapita (Rp)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="consumptionTableBody">
                                                <tr>
                                                    <td colspan="4" class="text-center py-5 text-muted">
                                                        <i class="fas fa-filter fa-3x mb-3 text-secondary opacity-50"></i>
                                                        <p class="mb-0">Silakan pilih Tahun, Triwulan, dan Wilayah terlebih
                                                            dahulu.</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const masterCoicops = {!! $coicops->toJson() !!};
            let parsedConsumption = [];

            // --- MASTER TAB LOGIC ---
            const pasteArea = document.getElementById('pasteArea');
            const previewBtn = document.getElementById('previewBtn');
            const clearBtn = document.getElementById('clearBtn');
            const saveBtn = document.getElementById('saveBtn');
            const tableBody = document.getElementById('coicopTableBody');
            let parsedData = [];

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    pasteArea.value = '';
                    parsedData = [];
                    saveBtn.classList.add('d-none');
                });
            }

            if (previewBtn) {
                previewBtn.addEventListener('click', () => {
                    const rawText = pasteArea.value.trim();
                    if (!rawText) {
                        Swal.fire('Info', 'Silakan paste data terlebih dahulu', 'info');
                        return;
                    }

                    const rows = rawText.split('\n');
                    const newRows = [];

                    rows.forEach((row, index) => {
                        const cols = row.split('\t');
                        if (cols.length >= 2) {
                            const kode = cols[0].trim();
                            const nama = cols[1].trim();
                            const seruti = cols[2] ? cols[2].trim() : '';

                            if (kode && nama) {
                                newRows.push({ kode, nama, seruti });
                            }
                        }
                    });

                    if (newRows.length === 0) {
                        Swal.fire('Error', 'Format data tidak dikenali. Pastikan copy-paste dari Excel dengan benar.', 'error');
                        return;
                    }

                    parsedData = newRows;
                    renderPreview(newRows);
                    saveBtn.classList.remove('d-none');
                    Swal.fire('Sukses', `${newRows.length} baris data dikenali. Klik Simpan untuk memproses.`, 'success');
                });
            }

            function renderPreview(data) {
                let html = '';
                data.forEach(item => {
                    html += `
                            <tr class="table-warning">
                                <td class="ps-4 fw-bold">${item.kode}</td>
                                <td>${item.nama}</td>
                                <td>${item.seruti}</td>
                                <td class="text-center"><span class="badge bg-warning text-dark">Preview (Belum Disimpan)</span></td>
                            </tr>
                        `;
                });
                tableBody.innerHTML = html;
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    if (parsedData.length === 0) return;

                    Swal.fire({
                        title: 'Simpan Data?',
                        text: "Data akan disimpan ke database (Update/Insert based on Kode).",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Simpan!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('{{ route("seruti.store-coicop") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ coicops: parsedData })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire('Gagal', data.message, 'error');
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                                });
                        }
                    });
                });
            }

            // --- INPUT CONSUMPTION LOGIC ---
            const pasteConsumption = document.getElementById('pasteConsumption');
            const previewConsumptionBtn = document.getElementById('previewConsumptionBtn');
            const clearConsumptionBtn = document.getElementById('clearConsumptionBtn');
            const saveConsumptionBtn = document.getElementById('saveConsumptionBtn');
            const consumptionTableBody = document.getElementById('consumptionTableBody');
            const tableHeaderTitle = document.getElementById('tableHeaderTitle');

            // Input Elements
            const inputYear = document.getElementById('inputYear');
            const inputQuarter = document.getElementById('inputQuarter');
            const inputKabupaten = document.getElementById('inputKabupaten');

            function updatePreviewHeader() {
                const y = inputYear ? inputYear.value : '';
                const q = inputQuarter && inputQuarter.value ? inputQuarter.options[inputQuarter.selectedIndex].text : '';

                let kName = '[Pilih Wilayah]';
                let kId = '';

                if (inputKabupaten && inputKabupaten.value) {
                    kName = inputKabupaten.options[inputKabupaten.selectedIndex].text;
                    kId = inputKabupaten.value;
                }

                if (tableHeaderTitle) {
                    tableHeaderTitle.innerText = `Data Tersimpan - ${kName}`;
                }

                if (y && y.length === 4 && inputQuarter.value && kId) {
                    fetchExistingData(y, inputQuarter.value, kId);
                } else {
                    consumptionTableBody.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-filter fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-0">Silakan pilih Tahun, Triwulan, dan Wilayah terlebih dahulu.</p>
                                </td>
                            </tr>
                        `;
                    pasteConsumption.value = '';
                }
            }

            function fetchExistingData(year, quarter, kabupatenId) {
                // Show loading state
                consumptionTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat data...</div></td></tr>';
                pasteConsumption.value = 'Memuat data...';
                saveConsumptionBtn.classList.add('d-none');

                const url = `{{ route('seruti.get-data') }}?year=${year}&quarter=${quarter}&kabupaten_id=${kabupatenId}`;

                fetch(url)
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            // Map response to include state
                            parsedConsumption = response.data.map(item => ({
                                ...item,
                                state: (item.value !== null && item.value !== '') ? 'saved' : 'empty'
                            }));

                            renderConsumptionTable(parsedConsumption);
                            populateTextArea(parsedConsumption);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                            consumptionTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>';
                            pasteConsumption.value = '';
                        }
                    })
                    .catch(err => {

                        console.error(err);
                        consumptionTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal menghubungi server.</td></tr>';
                        pasteConsumption.value = '';
                    });
            }

            function renderConsumptionTable(data) {
                let html = '';
                let hasData = false;

                data.forEach(item => {
                    const hasValue = item.value !== null && item.value !== '' && !isNaN(item.value);
                    if (hasValue) hasData = true;

                    const valDisplay = hasValue ? parseFloat(item.value).toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '-';

                    let rowClass = '';
                    if (item.state === 'ready') {
                        rowClass = 'table-info'; // Highlight changed rows
                    }

                    html += `
                            <tr class="${rowClass}">
                                <td class="ps-4 fw-bold align-middle">${item.kode}</td>
                                <td class="align-middle">${item.nama}</td>
                                <td class="align-middle">
                                    <span class="badge bg-light text-dark border">${item.seruti || '-'}</span>
                                </td>
                                <td class="text-end fw-bold align-middle ${hasValue ? 'text-dark' : 'text-muted'}">${valDisplay}</td>
                            </tr>
                        `;
                });

                if (data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Master data kosong.</td></tr>';
                }

                consumptionTableBody.innerHTML = html;

                // Always allow saving if we have parsed data (to allow updating)
                if (data.length > 0) {
                    saveConsumptionBtn.classList.remove('d-none');
                }
            }

            function populateTextArea(data) {
                // "jika ada konsumsi perkaipta nya maka tampil di inputan nilai"
                const lines = [];
                let hasValues = false;

                data.forEach(item => {
                    if (item.value !== null && item.value !== '' && !isNaN(item.value)) {
                        // Format nicely? Or raw? Excel usually prefers raw numbers or simple format.
                        // Let's use raw number for editing accuracy, but maybe format for readability?
                        // TextArea usually needs raw for re-parsing easily.
                        // User said "tampil di inputan nilai". 
                        lines.push(item.value);
                        hasValues = true;
                    } else {
                        lines.push(""); // Keep empty line to maintain alignment with Master
                    }
                });

                // Only fill if there is at least one value? Or always fill to show structure?
                // If we fill empty lines, it helps user know where to paste.
                // But empty lines might be confusing if they just want to paste a block.
                // However, logic relies on index matching. So we MUST maintain lines.

                if (hasValues) {
                    pasteConsumption.value = lines.join('\n');
                } else {
                    pasteConsumption.value = '';
                    pasteConsumption.placeholder = "Belum ada data nilai. Paste data dari Excel di sini...";
                }
            }

            if (inputYear) inputYear.addEventListener('input', updatePreviewHeader);
            if (inputQuarter) inputQuarter.addEventListener('change', updatePreviewHeader);
            if (inputKabupaten) inputKabupaten.addEventListener('change', updatePreviewHeader);

            // Trigger once on load
            updatePreviewHeader();

            if (clearConsumptionBtn) {
                clearConsumptionBtn.addEventListener('click', () => {
                    pasteConsumption.value = '';
                    // Do not clear parsedConsumption immediately, just the text. 
                    // Or clearing text should imply clearing preview?
                    // Usually Clear button clears the INPUT. The table shows DB data until overridden.
                    // But here, let's keep it simple.
                    pasteConsumption.focus();
                });
            }

            if (previewConsumptionBtn) {
                previewConsumptionBtn.addEventListener('click', () => {
                    const rawText = pasteConsumption.value.trim(); // Trim only start/end of whole text
                    if (!rawText) {
                        Swal.fire('Info', 'Silakan paste data excel terlebih dahulu', 'info');
                        return;
                    }

                    // We need to split by newline, preserving empty lines to maintain index alignment if user pasted a column with gaps.
                    // However, standard "trim()" above might kill empty lines at end.
                    // Let's use raw value.
                    const rawVal = pasteConsumption.value;
                    const rows = rawVal.split('\n');

                    // If user pasted just values (1 column), map to Master
                    // Logic: Map pasted rows 1-to-1 with masterCoicops

                    if (rows.length > masterCoicops.length) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Jumlah Baris Berlebih',
                            text: `Data paste memiliki ${rows.length} baris, sedangkan Master COICOP memiliki ${masterCoicops.length} baris. Data berlebih akan diabaikan.`,
                        });
                    }

                    // Reset parsedConsumption to Master Clone before applying updates
                    // This ensures we start fresh with the layout
                    const newParsed = JSON.parse(JSON.stringify(masterCoicops)).map(item => ({
                        ...item,
                        value: null,
                        state: 'empty'
                    }));

                    // If we previously had DB data, maybe we should merge?
                    // Usually "Preview" takes what is in the Text Area as the Source of Truth for "New Input".

                    let validCount = 0;

                    masterCoicops.forEach((coicop, index) => {
                        if (index < rows.length) {
                            let valueStr = rows[index].trim();

                            // Handling Excel formatting (dots as thousands, commas as decimals OR vice versa depending on locale)
                            // Assuming Indonesia Locale: 1.000.000,00
                            // Remove dots, replace comma with dot.

                            if (valueStr === '' || valueStr === '-') {
                                // Empty
                            } else {
                                let valueClean = valueStr.replace(/\./g, '').replace(/,/g, '.');
                                let value = parseFloat(valueClean);

                                if (!isNaN(value)) {
                                    newParsed[index].value = value;
                                    newParsed[index].state = 'ready';
                                    validCount++;
                                }
                            }
                        }
                    });

                    parsedConsumption = newParsed;
                    renderConsumptionTable(parsedConsumption);

                    if (validCount === 0) {
                        Swal.fire('Info', 'Tidak ada angka valid yang ditemukan dalam paste area.', 'info');
                    } else {
                        Swal.fire('Sukses', `${validCount} data nilai berhasil dibaca. Klik Simpan untuk melanjutkan.`, 'success');
                    }
                });
            }

            if (saveConsumptionBtn) {
                saveConsumptionBtn.addEventListener('click', () => {
                    const year = document.getElementById('inputYear').value;
                    const quarter = document.getElementById('inputQuarter').value;
                    const kabupatenId = document.getElementById('inputKabupaten').value;

                    if (!year || !quarter || !kabupatenId) {
                        Swal.fire('Peringatan', 'Harap pilih Tahun, Triwulan, dan Kabupaten terlebih dahulu.', 'warning');
                        return;
                    }

                    const dataToSave = parsedConsumption.filter(item =>
                        item.state === 'ready' || (item.state === 'saved' && item.value !== null)
                        // Actually we should save EVERYTHING that has a value, to ensure full sync.
                        // Or just 'ready' items?
                        // If we overwrite, we should save all non-nulls.
                    ).map(item => ({
                        kode: item.kode,
                        value: item.value
                    }));

                    if (dataToSave.length === 0) {
                        Swal.fire('Info', 'Tidak ada data nilai yang valid untuk disimpan.', 'info');
                        return;
                    }

                    Swal.fire({
                        title: 'Simpan Data Konsumsi?',
                        text: `Anda akan menyimpan ${dataToSave.length} data untuk ${year} Q${quarter}.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Simpan',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return fetch('{{ route("seruti.store") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    year: year,
                                    quarter: quarter,
                                    kabupaten_id: kabupatenId,
                                    data: dataToSave
                                })
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(response.statusText)
                                    }
                                    return response.json()
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(
                                        `Request failed: ${error}`
                                    )
                                })
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (result.value.success) {
                                Swal.fire('Berhasil!', result.value.message, 'success').then(() => {
                                    // Reload data to reflect saved state
                                    fetchExistingData(year, quarter, kabupatenId);
                                });
                            } else {
                                Swal.fire('Gagal', result.value.message || 'Error occurred', 'error');
                            }
                        }
                    });
                });
            }

        });
    </script>
    <style>
        .nav-pills .nav-link.active {
            background-color: var(--bps-blue);
        }

        .nav-pills .nav-link {
            color: #64748b;
        }
    </style>
@endpush