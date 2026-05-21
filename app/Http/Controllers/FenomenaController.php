<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Fenomena;
use App\Models\SektorUsaha;
use App\Models\Indikator;
use App\Models\JenisFenomena;
use App\Models\SumberBerita;

class FenomenaController extends Controller
{
    public function checkUniqueness(Request $request)
    {
        $field = $request->query('field');
        $value = $request->query('value');

        if (!in_array($field, ['judul', 'link_berita'])) {
            return response()->json(['valid' => false, 'message' => 'Field tidak valid'], 400);
        }

        $exists = Fenomena::where($field, $value)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? ($field == 'judul' ? 'Judul fenomena sudah pernah dimasukkan.' : 'Link berita sudah pernah digunakan.') : ''
        ]);
    }

    public function index(Request $request)
    {
        $query = Fenomena::with(['creator', 'verifier', 'sumberBerita', 'sektors', 'indikators'])
            ->where('status_verifikasi', 'Y');

        // ── Search ─────────────────────────────────────────────────
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('penjelasan', 'like', "%{$search}%");
            });
        }

        // ── Sektor ────────────────────────────────────────────────
        if ($sektor = $request->sektor) {
            $query->whereHas('sektors', fn($q) => $q->where('kode', $sektor));
        }

        // ── Indikator ─────────────────────────────────────────────
        if ($indikator = $request->indikator) {
            $query->whereHas('indikators', fn($q) => $q->where('kode', $indikator));
        }

        // ── Sumber Berita ─────────────────────────────────────────
        if ($sumber = $request->sumber) {
            $query->where('sumber_berita_id', $sumber);
        }

        // ── Tahun & Bulan ─────────────────────────────────────────
        if ($tahun = $request->tahun) {
            $query->where('tahun', $tahun);
        }
        if ($bulan = $request->bulan) {
            $query->where('bulan', $bulan);
        }

        // ── Creator (hanya jika login) ────────────────────────────
        if ($request->creator === 'me' && auth()->check()) {
            $query->where('created_by', auth()->id());
        }

        $query->orderBy('tanggal_berita', 'desc');
        $fenomenas = $query->paginate(10)->withQueryString();
        $totalFiltered = $fenomenas->total();
        $totalVerified = Fenomena::where('status_verifikasi', 'Y')->count();
        $myCount = auth()->check()
            ? Fenomena::where('status_verifikasi', 'Y')->where('created_by', auth()->id())->count()
            : 0;

        $sektors = SektorUsaha::orderBy('kode')->get();
        $indikators = Indikator::where('is_active', true)->orderBy('kode')->get();
        $sumberBeritas = SumberBerita::orderBy('nama')->get();
        $availableTahun = Fenomena::where('status_verifikasi', 'Y')
            ->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('fenomena.index', compact(
            'fenomenas',
            'sektors',
            'indikators',
            'sumberBeritas',
            'availableTahun',
            'totalFiltered',
            'totalVerified',
            'myCount'
        ));
    }

    public function create()
    {
        $sektorUsahas = SektorUsaha::orderBy('kode', 'asc')->get();
        $indikators = Indikator::where('kelompok', 'utama')
            ->where('is_active', true)
            ->orderBy('kode', 'asc')
            ->get();
        $jenisFenomenas = JenisFenomena::orderBy('nama', 'asc')->get();
        $sumberBeritas = SumberBerita::orderBy('nama', 'asc')->get();

        return view('fenomena.input', compact('sektorUsahas', 'indikators', 'jenisFenomenas', 'sumberBeritas'));
    }

    public function store(Request $request)
    {
        $rules = [
            'tanggal' => 'required|numeric|min:1|max:31',
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2000|max:' . date('Y'),
            'judul' => 'required|string|max:255|unique:fenomenas,judul',
            'penjelasan' => 'required|string',
            'sektor_usaha_id' => 'required|exists:sektor_usahas,id',
            'indikator_id' => 'required|exists:indikators,id,is_active,1',
            'jenis_fenomena_ids' => 'required|array',
            'jenis_fenomena_ids.*' => 'exists:jenis_fenomenas,id',
            'sumber_berita_id' => 'required|exists:sumber_beritas,id',
        ];

        // Conditional validation for link_berita
        $sumberBerita = \App\Models\SumberBerita::find($request->sumber_berita_id);
        if ($sumberBerita && $sumberBerita->is_online) {
            $rules['link_berita'] = 'required|url|unique:fenomenas,link_berita';
        } else {
            $rules['link_berita'] = 'nullable|url|unique:fenomenas,link_berita';
        }

        $request->validate($rules, [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.numeric' => 'Tanggal harus berupa angka.',
            'bulan.required' => 'Bulan wajib dipilih.',
            'tahun.required' => 'Tahun wajib diisi.',
            'judul.required' => 'Judul fenomena wajib diisi.',
            'judul.unique' => 'Judul fenomena sudah pernah dimasukkan.',
            'penjelasan.required' => 'Penjelasan fenomena wajib diisi.',
            'sektor_usaha_id.required' => 'Kode Lapangan Usaha wajib dipilih.',
            'sektor_usaha_id.exists' => 'Kode Lapangan Usaha tidak valid.',
            'indikator_id.required' => 'Kode Indikator wajib dipilih.',
            'indikator_id.exists' => 'Kode Indikator tidak valid.',
            'jenis_fenomena_ids.required' => 'Jenis fenomena wajib dipilih minimal satu.',
            'sumber_berita_id.required' => 'Sumber berita wajib dipilih.',
            'link_berita.required' => 'Link berita wajib diisi untuk sumber berita online.',
            'link_berita.url' => 'Format link berita tidak valid.',
            'link_berita.unique' => 'Link berita sudah pernah digunakan.',
        ]);

        $tanggal_berita = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, $request->tanggal);

        $fenomena = \App\Models\Fenomena::create([
            'tanggal_berita' => $tanggal_berita->toDateString(),
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'judul' => $request->judul,
            'penjelasan' => $request->penjelasan,
            'link_berita' => $request->link_berita,
            'sumber_berita_id' => $request->sumber_berita_id,
            'status_verifikasi' => 'P',
            'created_by' => auth()->id(),
        ]);

        $fenomena->sektors()->sync([$request->sektor_usaha_id]);
        $fenomena->indikators()->sync([$request->indikator_id]);
        $fenomena->jenisFenomenas()->sync($request->jenis_fenomena_ids);

        return redirect('/fenomena')->with('success', 'Data fenomena berhasil disimpan.');
    }

    public function kelola()
    {

        $sektorUsahas = \App\Models\SektorUsaha::with(['userAdd', 'userUpdate'])->withCount('fenomenas')->orderBy('kode', 'asc')->get();
        $indikators = \App\Models\Indikator::with(['userAdd', 'userUpdate'])
            ->withCount('fenomenas')
            ->orderByRaw("CASE WHEN kelompok = 'utama' THEN 1 ELSE 2 END")
            ->orderBy('kode', 'asc')
            ->get();
        $jenisFenomenas = \App\Models\JenisFenomena::with(['userAdd', 'userUpdate'])->withCount('fenomenas')->orderBy('nama', 'asc')->get();
        $sumberBeritas = \App\Models\SumberBerita::with(['userAdd', 'userUpdate'])->withCount('fenomenas')->orderBy('nama', 'asc')->get();
        return view('fenomena.kelola', compact('sektorUsahas', 'indikators', 'jenisFenomenas', 'sumberBeritas'));
    }

    public function visualisasi(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        $query = Fenomena::query();
        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        // Summary Cards
        $totalVerified = (clone $query)->where('status_verifikasi', 'Y')->count();
        $totalPending = (clone $query)->where('status_verifikasi', 'P')->count();
        $totalRejected = (clone $query)->where('status_verifikasi', 'T')->count();

        // --- Rekap Bulanan (Line Chart) ---
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanCounts = array_fill(1, 12, 0);
        $rekapBulanan = (clone $query)->where('status_verifikasi', 'Y')
            ->selectRaw('bulan, count(*) as total')
            ->groupBy('bulan')
            ->get();
        foreach ($rekapBulanan as $rb) {
            $bulanCounts[$rb->bulan] = $rb->total;
        }

        // --- Rekap Sektor (Top 10 Vertical Bar) ---
        $rekapSektor = SektorUsaha::withCount([
            'fenomenas' => function ($q) use ($tahun) {
                $q->where('status_verifikasi', 'Y');
                if ($tahun)
                    $q->where('tahun', $tahun);
            }
        ])
            ->orderBy('fenomenas_count', 'desc')
            ->take(10)
            ->get();

        // --- Rekap Indikator Utama (Top 10 Vertical Bar) ---
        $rekapIndikatorUtama = Indikator::where('is_active', true)
            ->where('kelompok', 'utama')
            ->withCount([
                'fenomenas' => function ($q) use ($tahun) {
                    $q->where('status_verifikasi', 'Y');
                    if ($tahun)
                        $q->where('tahun', $tahun);
                }
            ])
            ->orderBy('fenomenas_count', 'desc')
            ->take(10)
            ->get();

        // --- Rekap Indikator Dampak (Top 10 Vertical Bar) ---
        $rekapIndikatorDampak = Indikator::where('is_active', true)
            ->where('kelompok', 'dampak')
            ->withCount([
                'fenomenas' => function ($q) use ($tahun) {
                    $q->where('status_verifikasi', 'Y');
                    if ($tahun)
                        $q->where('tahun', $tahun);
                }
            ])
            ->orderBy('fenomenas_count', 'desc')
            ->take(10)
            ->get();

        // --- Rekap Sumber (Bar Chart) ---
        $rekapSumber = SumberBerita::withCount([
            'fenomenas' => function ($q) use ($tahun) {
                $q->where('status_verifikasi', 'Y');
                if ($tahun)
                    $q->where('tahun', $tahun);
            }
        ])
            ->orderBy('fenomenas_count', 'desc')
            ->get();

        // --- Rekap Jenis (Bar Chart) ---
        $rekapJenis = JenisFenomena::withCount([
            'fenomenas' => function ($q) use ($tahun) {
                $q->where('status_verifikasi', 'Y');
                if ($tahun)
                    $q->where('tahun', $tahun);
            }
        ])
            ->orderBy('fenomenas_count', 'desc')
            ->get();

        // --- Rekap Pemeriksaan (Donut) ---
        $pemeriksaanData = [
            'Verified' => $totalVerified,
            'Pending' => $totalPending,
            'Rejected' => $totalRejected
        ];

        $availableTahun = Fenomena::distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('fenomena.visualisasi', compact(
            'tahun',
            'availableTahun',
            'totalVerified',
            'totalPending',
            'totalRejected',
            'bulanCounts',
            'bulanLabels',
            'rekapSektor',
            'rekapIndikatorUtama',
            'rekapIndikatorDampak',
            'rekapSumber',
            'rekapJenis',
            'pemeriksaanData'
        ));
    }

    public function show($id)
    {
        $fenomena = Fenomena::with([
            'creator',
            'verifier',
            'sumberBerita',
            'sektors',
            'indikators',
            'jenisFenomenas',
        ])->findOrFail($id);

        // from = 'verification' | 'fenomena' (default)
        $from = request('from', 'fenomena');

        return view('fenomena.show', compact('fenomena', 'from'));
    }

}
