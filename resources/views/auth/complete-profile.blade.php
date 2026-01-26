@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header / Logo -->
        <div class="w-full max-w-md text-center mb-8">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png"
                alt="Logo BPS" class="h-16 mx-auto mb-4">
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">
                Lengkapi Profil Anda
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Data ini diperlukan untuk administrasi sistem internal BPS.
            </p>
        </div>

        <div class="w-full max-w-md">
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <!-- Blue Header Bar -->
                <div class="h-2 bg-blue-600 w-full"></div>

                <div class="p-8">
                    <!-- Status Alert -->
                    @if($user->status == 'pending' && $user->no_hp && $user->kabupaten_id)
                        <div class="mb-6 bg-orange-50 border-l-4 border-orange-400 p-4 rounded-r-md">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-orange-400 mt-0.5"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-orange-800">Menunggu Verifikasi</h3>
                                    <p class="mt-1 text-sm text-orange-700 leading-relaxed">
                                        Akun Anda sedang dalam proses verifikasi oleh Admin BPS Provinsi. <br>
                                        Silakan menunggu hingga akun diaktifkan.
                                    </p>
                                    <p class="mt-2 text-xs text-orange-600 font-medium">
                                        Estimasi verifikasi: 1–2 hari kerja
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form class="space-y-6" action="{{ route('complete-profile.update') }}" method="POST">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <div class="relative">
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                    class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors {{ $user->status == 'pending' && $user->no_hp ? 'text-gray-500 cursor-not-allowed bg-gray-100' : '' }}"
                                    {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                                @if($user->status == 'pending' && $user->no_hp)
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- WhatsApp / HP -->
                        <div>
                            <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp / Nomor
                                Telepon</label>
                            <div class="relative">
                                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                                    placeholder="Contoh: 08123456789" required
                                    class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors {{ $user->status == 'pending' && $user->no_hp ? 'text-gray-500 cursor-not-allowed bg-gray-100' : '' }}"
                                    {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                                @if($user->status == 'pending' && $user->no_hp)
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                            @error('no_hp')
                                <p class="mt-1 text-xs text-red-600 flex items-center gap-1"><i
                                        class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kabupaten -->
                        <div>
                            <label for="kabupaten_id" class="block text-sm font-medium text-gray-700 mb-1">Kabupaten /
                                Kota</label>
                            <div class="relative">
                                <select id="kabupaten_id" name="kabupaten_id" required
                                    class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none {{ $user->status == 'pending' && $user->no_hp ? 'text-gray-500 cursor-not-allowed bg-gray-100' : '' }}"
                                    {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Wilayah --</option>
                                    @foreach($kabupatens as $kab)
                                        <option value="{{ $kab->id }}" {{ old('kabupaten_id', $user->kabupaten_id) == $kab->id ? 'selected' : '' }}>
                                            [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    @if($user->status == 'pending' && $user->no_hp)
                                        <i class="fas fa-lock text-gray-400"></i>
                                    @else
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    @endif
                                </div>
                            </div>
                            @error('kabupaten_id')
                                <p class="mt-1 text-xs text-red-600 flex items-center gap-1"><i
                                        class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tim Kerja -->
                        <div>
                            <label for="team" class="block text-sm font-medium text-gray-700 mb-1">Tim / Unit Kerja</label>
                            <div class="relative">
                                <select id="team" name="team" required
                                    class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none {{ $user->status == 'pending' && $user->no_hp ? 'text-gray-500 cursor-not-allowed bg-gray-100' : '' }}"
                                    {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                                    <option value="Statistik Sosial" {{ old('team', $user->team) == 'Statistik Sosial' ? 'selected' : '' }}>Statistik Sosial</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    @if($user->status == 'pending' && $user->no_hp)
                                        <i class="fas fa-lock text-gray-400"></i>
                                    @else
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($user->status == 'pending' && $user->no_hp)
                            <p class="text-xs text-gray-500 text-center bg-gray-50 p-2 rounded border border-gray-100">
                                <i class="fas fa-info-circle mr-1"></i> Data tidak dapat diubah selama proses verifikasi.
                            </p>
                        @endif

                        @if($user->status != 'pending' || !$user->no_hp)
                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5">
                                    Simpan & Lanjutkan
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Footer / Logout -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-center">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="text-sm font-medium text-gray-500 hover:text-blue-600 flex items-center gap-2 transition-colors focus:outline-none">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-8">
                &copy; {{ date('Y') }} Badan Pusat Statistik Provinsi Kalimantan Barat. All
                rights reserved.
            </p>
            <p class="text-center text-xs text-gray-400 mt-2">Jika terdapat pertanyaan atau error - bug pada sistem, harap
                hubungi Developer dengan <a href="https://wa.me/6289529406362" target="_blank"
                    class="text-decoration-none fw-bold" style="color: var(--bps-orange);">klik disini</a>
            </p>
        </div>
    </div>
@endsection