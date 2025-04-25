<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UnsetAdminMode
{
    /**
     * Обрабатывает входящий запрос.
     */
    public function handle(Request $request, Closure $next)
    {
        // Если текущий URL не начинается с "admin_panel",
        // сбрасываем режим администратора.
        if (!str_starts_with($request->path(), 'admin_panel')) {
            $request->session()->forget('admin_mode');
        }
        return $next($request);
    }
}
