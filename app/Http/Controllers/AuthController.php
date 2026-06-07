<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Redirige vers Google pour l'authentification.
     */
    public function redirectToGoogle()
    {
        // If there's an auth error and no force flag, show the login page with error
        if (session('auth_error') && !request('force')) {
            return view('auth.login', ['error' => session('auth_error')]);
        }
        return Socialite::driver('google')->redirect();
    }

    /**
     * Gère le callback Google et la restriction de domaine.
     */
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();

        if (!Str::endsWith($email, '@groupe-speed.cloud')) {
            return redirect('/login')->with('auth_error', 'Seuls les comptes @groupe-speed.cloud sont autorisés.');
        }

        $blacklist = array_filter(array_map('trim', explode(',', env('AUTH_BLACKLIST', ''))));
        if (in_array(strtolower($email), array_map('strtolower', $blacklist))) {
            return redirect()->route('forbidden')->with('blocked_email', $email);
        }
        $user = User::firstOrCreate([
            'email' => $email
        ], [
            'name' => $googleUser->getName(),
            'password' => bcrypt(Str::random(32)),
        ]);
        Auth::login($user, true);
        return redirect('/dashboard');
    }

    /**
     * Déconnexion sécurisée.
     */
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }
}
