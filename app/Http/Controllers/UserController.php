<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register_post(RegisterRequest $request)
    {
        $requests = $request->validated();
        $requests['password'] = Hash::make($requests['password']);
        User::create($requests);
        return redirect()->route('auth');
    }

    public function auth_post(AuthRequest $request)
{
    if (Auth::attempt($request->validated())) {
        $request->session()->regenerate();
        return redirect()->route('/');
    }

    return back()->with(['authError' => true]);
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->regenerate();
        return redirect()->route('/');
    }
}
