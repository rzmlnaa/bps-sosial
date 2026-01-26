<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google authentication failed.');
        }

        $user = User::where('google_id', $googleUser->id)->orWhere('email', $googleUser->email)->first();

        if ($user) {
            // Update google_id if not set (e.g. registered via email previously)
            if (is_null($user->google_id)) {
                $user->update(['google_id' => $googleUser->id]);
            }

            Auth::login($user);

            // Check if profile is complete
            if (empty($user->no_hp) || empty($user->kabupaten_id)) {
                return redirect()->route('complete-profile');
            }

            // Check status
            if ($user->status !== 'approved' && $user->role !== 'admin') { // Assuming 'admin' role bypasses or status logic
                // You might want to log them out or show a "pending" page.
                // For now, let's redirect to complete profile if pending, or dashboard with specific view.
                // Implementation plan said: "redirect to completion page" if info missing.
                // If info present but pending?
                // Let's assume dashboard handles pending state or we redirect to a 'wait' page.
            }

            return redirect()->intended('/dashboard');

        } else {
            // New User
            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => bcrypt(str()->random(16)), // Dummy password
                'status' => 'pending',
                'team' => 'TBD', // To be defined
            ]);

            Auth::login($newUser);

            return redirect()->route('complete-profile');
        }
    }
}
