<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
public function toggleMode(Request $request)
    {
        $newMode = !$request->session()->get('admin_mode', false);
        $request->session()->put('admin_mode', $newMode);

        // Если новый режим — обычный (админ-режим выключен), перенаправляем на главную страницу
        if (!$newMode) {
            return redirect('/');
        }
        
        return redirect()->back();
    }
}
