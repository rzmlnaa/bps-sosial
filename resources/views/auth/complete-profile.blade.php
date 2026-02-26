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


        <div class="w-full max-w-lg">

            <!-- Stepper -->
            <div class="flex items-center justify-center mb-8">
                @php
                    $isVerified = !empty($user->no_hp_verified_at);
                @endphp
                <!-- Step 1 -->
                <div class="flex items-center">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full border-2 {{ !$showOtpStep && !$isVerified ? 'border-blue-600 bg-blue-600 text-white' : 'border-green-500 bg-green-500 text-white' }} font-bold text-sm">
                        @if($showOtpStep || $isVerified) <i class="fas fa-check"></i> @else 1 @endif
                    </div>
                    <div
                        class="ml-2 text-sm font-medium {{ !$showOtpStep && !$isVerified ? 'text-blue-600' : 'text-green-500' }}">
                        Data Diri
                    </div>
                </div>

                <!-- Line -->
                <div class="w-12 h-1 mx-4 {{ $showOtpStep || $isVerified ? 'bg-green-500' : 'bg-gray-200' }}"></div>

                <!-- Step 2 -->
                <div class="flex items-center">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full border-2 {{ $showOtpStep ? 'border-blue-600 bg-blue-600 text-white' : ($isVerified ? 'border-green-500 bg-green-500 text-white' : 'border-gray-200 text-gray-500') }} font-bold text-sm">
                        @if($isVerified) <i class="fas fa-check"></i> @else 2 @endif
                    </div>
                    <div
                        class="ml-2 text-sm font-medium {{ $showOtpStep ? 'text-blue-600' : ($isVerified ? 'text-green-500' : 'text-gray-500') }}">
                        Verifikasi
                        WA</div>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <!-- Blue Header Bar -->
                <div class="h-2 bg-blue-600 w-full"></div>

                <div class="p-8">
                    <!-- Status Alert -->
                    @if($user->status == 'pending' && $user->no_hp_verified_at)
                        <div class="mb-6 bg-orange-50 border-l-4 border-orange-400 p-4 rounded-r-md">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-orange-400 mt-0.5"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-orange-800">Menunggu Persetujuan Admin</h3>
                                    <p class="mt-1 text-sm text-orange-700 leading-relaxed">
                                        Nomor WhatsApp Anda telah terverifikasi. <br>
                                        Akun sedang menunggu persetujuan akhir dari Admin.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Fail/Error Message -->
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
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

                    @if(!$showOtpStep)
                        <!-- STEP 1: FORM INPUT DATA -->
                        <form class="space-y-6" action="{{ route('complete-profile.update') }}" method="POST">
                            @csrf

                            <!-- Nama Lengkap -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <div class="relative">
                                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                        class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        {{ $user->status == 'pending' && $user->no_hp_verified_at ? 'disabled' : '' }}>
                                </div>
                            </div>

                            <!-- WhatsApp / HP -->
                            <div>
                                <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp / Nomor
                                    Telepon</label>
                                <div class="relative">
                                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                                        placeholder="Contoh: 08123456789" required
                                        class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        {{ $user->status == 'pending' && $user->no_hp_verified_at ? 'disabled' : '' }}>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Kode OTP akan dikirim ke nomor ini via WhatsApp.</p>
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
                                        class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none"
                                        {{ $user->status == 'pending' && $user->no_hp_verified_at ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Wilayah --</option>
                                        @foreach($kabupatens as $kab)
                                            <option value="{{ $kab->id }}" {{ old('kabupaten_id', $user->kabupaten_id) == $kab->id ? 'selected' : '' }}>
                                                [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
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
                                        class="block w-full px-4 py-3 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none"
                                        {{ $user->status == 'pending' && $user->no_hp_verified_at ? 'disabled' : '' }}>
                                        <option value="Statistik Sosial" {{ old('team', $user->team) == 'Statistik Sosial' ? 'selected' : '' }}>Statistik Sosial</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            @if(!($user->status == 'pending' && $user->no_hp_verified_at))
                                <div class="pt-2">
                                    <button type="submit"
                                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5">
                                        Simpan & Kirim OTP <i class="fas fa-arrow-right ml-2 mt-0.5"></i>
                                    </button>
                                </div>
                            @endif
                        </form>
                    @else
                        <!-- STEP 2: OTP VERIFICATION -->
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                                <i class="fas fa-mobile-alt text-2xl text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Masukkan Kode OTP</h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Kode verifikasi sementara dikirim disini
                            </p>
                            @if(session('success'))
                                <h1 class="font-bold text-success mt-2">{{ $user->otp_code }}</h1>
                            @endif
                            <form action="{{ route('complete-profile.reset-number') }}" method="POST" class="inline-block mt-2">
                                @csrf
                                <input type="hidden" name="name" value="{{ $user->name }}">
                                <input type="hidden" name="no_hp" value="{{ $user->no_hp }}">
                                <!-- Keep raw value or formatted? -->
                                <input type="hidden" name="kabupaten_id" value="{{ $user->kabupaten_id }}">
                                <input type="hidden" name="team" value="{{ $user->team }}">
                                <button type="submit"
                                    class="text-xs text-blue-600 hover:text-blue-800 underline bg-transparent border-0 p-0 cursor-pointer">
                                    (Bukan nomor Anda? Ganti nomor)
                                </button>
                            </form>
                        </div>

                        <form class="space-y-6" action="{{ route('complete-profile.verify-otp') }}" method="POST">
                            @csrf
                            <div>
                                <label for="otp_code" class="sr-only">Kode OTP</label>
                                <input type="text" id="otp_code" name="otp_code" placeholder="0 0 0 0 0 0"
                                    class="block w-full text-center text-3xl tracking-widest px-4 py-4 rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono"
                                    maxlength="6" required autofocus>
                                @error('otp_code')
                                    <p class="mt-1 text-xs text-red-600 flex items-center justify-center gap-1"><i
                                            class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5">
                                    Verifikasi & Selesai <i class="fas fa-check ml-2 mt-0.5"></i>
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 text-center">
                            <p class="text-sm text-gray-600">
                                Tidak menerima kode?
                            </p>
                            <form action="{{ route('complete-profile.resend-otp') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-500 focus:outline-none">
                                    Kirim Ulang OTP
                                </button>
                            </form>
                        </div>
                    @endif

                </div>

                <!-- Footer / Logout -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-center">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="setTimeout(() => window.location.href = '/login', 50)"
                            class="text-sm font-medium text-gray-500 hover:text-blue-600 flex items-center gap-2 transition-colors focus:outline-none">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <footer class="mt-auto pt-4 border-top text-center text-muted pb-0">
                <small class="d-block mb-1">&copy; {{ date('Y') }} Badan Pusat Statistik Provinsi Kalimantan Barat. All
                    rights reserved.</small>
                <small>
                    Jika terdapat <span class="text-danger fw-bold">pertanyaan</span> atau <span
                        class="text-danger fw-bold">error - bug</span> pada sistem,
                    harap hubungi Developer dengan
                    <a href="https://kostapp.reservasiaja.com/portofolio" target="_blank"
                        class="text-decoration-none fw-bold" style="color: var(--bps-orange);">
                        klik disini
                    </a>
                </small>
            </footer>
        </div>
    </div>
@endsection