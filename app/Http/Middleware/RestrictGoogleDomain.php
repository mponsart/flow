<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class RestrictGoogleDomain
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        // Vérification du domaine
        $domain = config('services.auth.domain', '@groupe-speed.cloud');
        if (!Str::endsWith($user->email, $domain)) {
            Auth::logout();
            return redirect()->route('forbidden')->with('blocked_email', $user->email);
        }

        // Whitelist : si définie, seuls les comptes listés ont accès
        $whitelist = config('services.auth.whitelist', []);
        if (!empty($whitelist) && !in_array(strtolower($user->email), array_map('strtolower', $whitelist))) {
            $email = $user->email;
            Auth::logout();
            return redirect()->route('forbidden')->with('blocked_email', $email);
        }

        return $next($request);
    }
}
