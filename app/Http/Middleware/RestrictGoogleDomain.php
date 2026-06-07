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

        if (!Str::endsWith($user->email, '@groupe-speed.cloud')) {
            Auth::logout();
            return redirect('/login')->with('auth_error', 'Seuls les comptes @groupe-speed.cloud sont autorisés.');
        }

        $blacklist = array_filter(array_map('trim', explode(',', env('AUTH_BLACKLIST', ''))));
        if (in_array(strtolower($user->email), array_map('strtolower', $blacklist))) {
            $email = $user->email;
            Auth::logout();
            return redirect()->route('forbidden')->with('blocked_email', $email);
        }

        return $next($request);
    }
}
