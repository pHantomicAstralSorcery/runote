<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAccess
{
    public function handle(Request $request, Closure $next)
    {
        $link = $request->route('slug')
            ? \App\Models\NamedLink::where('slug', $request->route('slug'))->first()
            : null;

        if (!$link || !$link->active || now()->lt($link->open_at) || now()->gt($link->close_at)) {
            abort(403, 'Доступ к тетради ограничен');
        }

        return $next($request);
    }
}
