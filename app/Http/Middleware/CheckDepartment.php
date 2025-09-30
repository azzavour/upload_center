<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckDepartment
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!$user->hasDepartment()) {
            return redirect()->back()->with('error', 'Anda belum terdaftar di department manapun. Hubungi administrator.');
        }

        return $next($request);
    }
}