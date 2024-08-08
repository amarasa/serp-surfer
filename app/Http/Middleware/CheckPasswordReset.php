<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPasswordReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->force_password_reset) {
            Auth::logout();
            return redirect()->route('password.request')->withErrors([
                'email' => 'You must reset your password before logging in.'
            ]);
        }

        return $next($request);
    }
}
