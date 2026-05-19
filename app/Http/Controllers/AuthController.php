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
            return redirect('/login')->withErrors(['email' => 'Seuls les comptes @groupe-speed.cloud sont autorisés.']);
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
