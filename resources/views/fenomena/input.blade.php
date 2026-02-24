@extends('layouts.admin')

@section('title', 'Input Fenomena')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Data Fenomena</h2>
            <p class="text-muted mb-0">Fenomena Sosial Ekonomi & Kejadian Penting</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('fenomena.index') }}" class="btn btn-outline-secondary shadow-sm"
                style="border-radius: 8px;">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="card-body">
                    <!-- Link Input Section -->
                    <div class="mb-4 p-3 bg-light rounded border">
                        <label for="link" class="form-label fw-bold">Copy - Paste Berita (Opsional)</label>
                        <div class="input-group">
                            <textarea type="url" class="form-control" placeholder="Copy - Paste Berita"></textarea>
                            <button class="btn btn-success text-white fw-bold" type="button" onclick="ambilBerita()"
                                id="btn-fetch">
                                <i class="fas fa-magic me-1"></i> Ambil Otomatis
                            </button>
                        </div>
                        <small class="text-muted">Copy - Paste berita dan klik "Ambil Otomatis" untuk mengisi form
                            secara otomatis.</small>
                    </div>

                    <form action="#" method="POST" id="form-fenomena">
                        @csrf
                        <label for="link" class="form-label fw-bold">Link Berita</label>
                        <input type="url" class="form-control" id="link" placeholder="https://kompas.com/..."
                            aria-label="Link Berita">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal" class="form-label fw-bold">Tanggal</label>
                                    <input type="number" class="form-control" id="tanggal" name="tanggal" min="1" max="31"
                                        placeholder="DD" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bulan" class="form-label fw-bold">Bulan</label>
                                    <select class="form-select" id="bulan" name="bulan" required>
                                        <option value="">Pilih Bulan</option>
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tahun" class="form-label fw-bold">Tahun</label>
                                    <input type="number" class="form-control" id="tahun" name="tahun" min="2000"
                                        max="{{ date('Y') }}" value="{{ date('Y') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="judul" class="form-label fw-bold">Judul Fenomena</label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>

                        <div class="mb-3">
                            <label for="penjelasan" class="form-label fw-bold">Penjelasan Fenomena</label>
                            <textarea class="form-control" id="penjelasan" name="penjelasan" rows="5" required></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('fenomena.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" disabled
                                title="Tabel belum dibuat, hanya simulasi">Simpan (Demo)</button>
                        </div>
                    </form>
    </div>



    @push('scripts')
        <script>
            async function ambilBerita() {

                const btn = document.getElementById("btn-fetch");
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Membaca...';
                btn.disabled = true;

                try {
                    const text = await navigator.clipboard.readText();

                    if (!text || text.length < 50)
                        throw new Error("Clipboard kosong");

                    // ===================== LINK =====================
                    const linkMatch = text.match(/https?:\/\/[^\s]+/g);
                    if (linkMatch) {
                        document.getElementById("link").value = linkMatch[0];
                    }

                    // ===================== TANGGAL =====================
                    const bulanMap = {
                        januari: 1, februari: 2, maret: 3, april: 4, mei: 5, juni: 6,
                        juli: 7, agustus: 8, september: 9, oktober: 10, november: 11, desember: 12
                    };

                    const dateMatch = text.match(/(\d{1,2})\s(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s(\d{4})/i);

                    if (dateMatch) {
                        document.getElementById("tanggal").value = dateMatch[1];
                        document.getElementById("bulan").value = bulanMap[dateMatch[2].toLowerCase()];
                        document.getElementById("tahun").value = dateMatch[3];
                    }

                    // ===================== JUDUL =====================
                    // ambil sebelum nama media (KOMPAS.com / detikcom / dll)
                    let judul = text.split(/kompas\.com|detikcom|tribunnews|tempo\.co/i)[0];
                    judul = judul.split('\n')[0].trim();

                    document.getElementById("judul").value = judul;

                    // ===================== ISI BERITA =====================

                    // cari awal artikel: "KOTA (MEDIA) -"
                    let startIndex = text.search(/[A-Z][A-Z\s]+ \([A-Z]+\) -/);

                    let isi = "";

                    if (startIndex !== -1) {
                        isi = text.substring(startIndex);
                    } else {
                        // fallback kalau tidak ada pola kota
                        isi = text;
                    }

                    // hapus judul & metadata awal
                    isi = isi.replace(/^.*?\) -\s*/, '');

                    // pecah paragraf
                    let paragraf = isi.split('\n')
                        .map(l => l.trim())
                        .filter(l =>
                            l.length > 100 &&                // hanya paragraf panjang
                            !l.includes('ANTARA') &&         // buang kredit foto
                            !l.includes('waktu baca') &&
                            !l.match(/^\w+\s\d{1,2},/)       // buang tanggal ulang
                        );

                    // ambil 3 paragraf pertama
                    isi = paragraf.slice(0, 3).join(' ');

                    // rapikan spasi
                    isi = isi.replace(/\s+/g, ' ').trim();

                    document.getElementById("penjelasan").value = isi;


                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Fenomena otomatis terisi',
                        timer: 1500,
                        showConfirmButton: false
                    });

                } catch (e) {
                    Swal.fire('Gagal', 'Copy seluruh isi berita dulu (CTRL+A lalu CTRL+C)', 'error');
                }

                btn.innerHTML = '<i class="fas fa-magic me-1"></i> Ambil Otomatis';
                btn.disabled = false;
            }
        </script>
    @endpush
@endsection