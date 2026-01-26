@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-blue-900">
                Lengkapi Profil Anda
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Silakan lengkapi data berikut untuk melanjutkan penggunaan sistem.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border-t-4 border-blue-600">

                @if(session('success'))
                    <div class="rounded-md bg-green-50 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <!-- Heroicon name: solid/check-circle -->
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($user->status == 'pending' && $user->no_hp && $user->kabupaten_id)
                    <div class="rounded-md bg-orange-50 p-4 mb-6 border border-orange-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <!-- Heroicon name: solid/exclamation -->
                                <svg class="h-5 w-5 text-orange-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-orange-800">Status: Pending Verifikasi</h3>
                                <div class="mt-2 text-sm text-orange-700">
                                    <p>Akun Anda sedang dalam proses verifikasi oleh administrator. Silakan menunggu hingga
                                        status diaktifkan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form class="space-y-6" action="{{ route('complete-profile.update') }}" method="POST">
                    @csrf

                    <!-- Status Badge -->
                    <div class="flex justify-center mb-6">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $user->status == 'pending' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                            Status: {{ ucfirst($user->status ?? 'Guest') }}
                        </span>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nama Lengkap
                        </label>
                        <div class="mt-1">
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                            <p class="mt-1 text-xs text-gray-500">Nama dapat diubah sesuai kebutuhan.</p>
                        </div>
                    </div>

                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700">
                            WhatsApp / Nomor Telepon
                        </label>
                        <div class="mt-1">
                            <input id="no_hp" name="no_hp" type="text" placeholder="08xxxxxxxxxx"
                                value="{{ old('no_hp', $user->no_hp) }}" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                            @error('no_hp')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="kabupaten_id" class="block text-sm font-medium text-gray-700">
                            Kabupaten / Kota
                        </label>
                        <div class="mt-1">
                            <select id="kabupaten_id" name="kabupaten_id" required
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                                <option value="">Pilih Kabupaten / Kota</option>
                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}" {{ old('kabupaten_id', $user->kabupaten_id) == $kab->id ? 'selected' : '' }}>
                                        [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kabupaten_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="team" class="block text-sm font-medium text-gray-700">
                            Tim / Unit Kerja
                        </label>
                        <div class="mt-1">
                            <select id="team" name="team" required
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                {{ $user->status == 'pending' && $user->no_hp ? 'disabled' : '' }}>
                                <option value="Statistik Sosial" {{ old('team', $user->team) == 'Statistik Sosial' ? 'selected' : '' }}>Statistik Sosial</option>
                            </select>
                            @error('team')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if($user->status != 'pending' || !$user->no_hp)
                        <div>
                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                Simpan & Lanjutkan
                            </button>
                        </div>
                    @endif
                </form>

                <div class="mt-6 border-t border-gray-200 pt-6">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full flex justify-center items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection