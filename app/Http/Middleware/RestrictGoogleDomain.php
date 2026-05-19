<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class RestrictGoogleDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && !Str::endsWith($user->email, '@groupe-speed.cloud')) {
            Auth::logout();
            return redirect('/login')->withErrors(['email' => 'Seuls les comptes @groupe-speed.cloud sont autorisés.']);
        }
        return $next($request);
    }
}
