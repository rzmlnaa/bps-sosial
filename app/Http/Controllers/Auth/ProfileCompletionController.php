<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileCompletionController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // If active, redirect to dashboard
        if ($user->status === 'active') {
            return redirect('/dashboard');
        }

        $kabupatens = Kabupaten::withoutIndonesia()->orderBy('kode_kab', 'asc')->get();

        // If user has OTP pending (and phone verified is null), show OTP step
        $showOtpStep = !empty($user->otp_code) && is_null($user->no_hp_verified_at);

        return view('auth.complete-profile', compact('user', 'kabupatens', 'showOtpStep'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!empty($user->otp_code) && is_null($user->no_hp_verified_at)) {
            return back()->with('error', 'Anda sudah mengirimkan kode OTP.');
        }
        $rawNoHp = $request->no_hp;

        // Hapus semua selain angka
        $noHp = preg_replace('/[^0-9]/', '', $rawNoHp);

        // Jika diawali 0 → ganti 62
        if (str_starts_with($noHp, '0')) {
            $noHp = '62' . substr($noHp, 1);
        }
        $request->merge([
            'no_hp' => $noHp
        ]);
        $request->validate([
            'name' => 'required|string|max:255',
            'no_hp' => [
                'required',
                'numeric',
                Rule::unique('users', 'no_hp')->ignore(auth()->id()),
            ],
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'team' => 'required|string',
        ], [
            'name.required' => 'Nama Lengkap wajib diisi.',
            'no_hp.required' => 'Nomor WhatsApp wajib diisi.',
            'no_hp.numeric' => 'Nomor WhatsApp hanya boleh berisi angka.',
            'no_hp.unique' => 'Nomor WhatsApp sudah digunakan oleh pengguna lain.',
            'kabupaten_id.required' => 'Silakan pilih Kabupaten/Kota.',
            'team.required' => 'Silakan pilih Unit Kerja.',
        ]);

        //$user = Auth::user();




        /** @var \App\Models\User $user */

        // Save profile data but keep status/verified pending
        $user->update([
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'kabupaten_id' => $request->kabupaten_id,
            'team' => $request->team,
            'role' => 'user', // Ensure default role
            // Status remains whatever it was or 'pending' initiation? 
            // We don't set status to 'pending' yet until phone is verified?
            // Actually, if they update profile, they are starting the process.
            // But if they are just updating info, we generate OTP.
        ]);

        // Generate and Send OTP
        $this->generateAndSendOtp($user);


        return redirect('/complete-profile')->with('success', 'Sementara Kode OTP dikirim disini, kode Anda adalah ' . $user->otp_code . '  Silakan masukkan kode untuk verifikasi.');
    }

    public function verifyOtp(Request $request)
    {

        $request->validate([
            'otp_code' => 'required|string|size:6',
        ], [
            'otp_code.required' => 'Kode OTP wajib diisi.',
            'otp_code.size' => 'Kode OTP harus 6 digit.',
        ]);

        $user = Auth::user();
        /** @var \App\Models\User $user */

        if (!$user->otp_code || !$user->otp_expires_at) {
            return back()->with('error', 'Tidak ada permintaan OTP yang aktif. Silakan minta kode baru.');
        }

        if (\Carbon\Carbon::now()->gt($user->otp_expires_at)) {
            return back()->with('error', 'Kode OTP telah kedaluwarsa. Silakan minta kode baru.');
        }

        if ($request->otp_code !== $user->otp_code) {
            return back()->with('error', 'Kode OTP salah. Silakan coba lagi.');
        }

        // OTP Valid
        $user->update([
            'no_hp_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
            'status' => 'pending' // Now set status to pending for Admin approval
        ]);

        return redirect('/complete-profile')->with('success', 'Nomor HP berhasil diverifikasi. Akun Anda sekarang menunggu persetujuan Admin.');
    }

    public function resendOtp()
    {

        $user = Auth::user();
        if (!$user->otp_code || !$user->otp_expires_at) {
            return back()->with('error', 'Tidak ada permintaan OTP yang aktif. Silakan minta kode baru.');
        }
        if (\Carbon\Carbon::now()->lt($user->otp_expires_at)) {
            $remaining = \Carbon\Carbon::now()->diffInSeconds($user->otp_expires_at);
            return back()->with('error', 'Silakan tunggu ' . gmdate('i:s', $remaining) . ' sebelum mengirim ulang kode OTP.');
        }

        if (empty($user->no_hp)) {
            return back()->with('error', 'Nomor HP belum terdaftar. Silakan isi data profil kembali.');
        }

        $this->generateAndSendOtp($user);

        return back()->with('success', 'Kode OTP baru telah dikirim disini, Kode Anda adalah Silakan masukkan kode untuk verifikasi.');
    }

    public function resetNumber()
    {

        $user = Auth::user();

        // Check if OTP is still valid (not expired)
        if ($user->otp_expires_at && \Carbon\Carbon::now()->lt($user->otp_expires_at)) {
            $remaining = \Carbon\Carbon::now()->diffInSeconds($user->otp_expires_at);
            return back()->with('error', 'Harap tunggu ' . gmdate('i:s', $remaining) . ' sebelum mengganti nomor.');
        }

        // Allow reset
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        return redirect()->route('complete-profile');
    }

    private function generateAndSendOtp($user)
    {
        $otp = rand(100000, 999999);
        $expiresAt = \Carbon\Carbon::now()->addMinutes(5);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt
        ]);

        // $message = "Halo {$user->name},\n\n";
        // $message .= "Kode OTP verifikasi Anda adalah: *{$otp}*\n";
        // $message .= "Kode ini berlaku selama 5 menit.\n\n";
        // $message .= "Jangan berikan kode ini kepada siapapun.\n\n";
        // $message .= "*Salam,* \n";
        // $message .= "*Tim Sosial BPS Provinsi Kalbar*";

        // \App\Helpers\WhatsAppHelper::kirimPesanWhatsApp($user->no_hp, $message);
    }
}
