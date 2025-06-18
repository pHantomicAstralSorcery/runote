<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Notebook;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Switch between admin and user mode.
     */
    public function toggleMode(Request $request)
    {
        $newMode = !$request->session()->get('admin_mode', false);
        $request->session()->put('admin_mode', $newMode);

        if (!$newMode) {
            return redirect('/');
        }
        
        return redirect()->back();
    }
    

    /**
     * Display a listing of all users.
     */
    public function users(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()
            ->when($search, function ($query, $search) {
                return $query->where('login', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);
        // Путь к файлу пользователей в админке
        return view('admin_panel.users', compact('users'));
    }

    /**
     * Display a listing of all quizzes.
     */
    public function quizzes(Request $request)
    {
        $search = $request->input('search');
        
        $quizzes = Quiz::with('user')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                             ->orWhereHas('user', function ($q) use ($search) {
                                 $q->where('login', 'like', "%{$search}%");
                             });
            })
            ->latest()
            ->paginate(15);

        // Путь к файлу тестов в админке
        return view('admin_panel.quizzes', compact('quizzes'));
    }

    /**
     * Display a listing of all notebooks.
     */
    public function notebooks(Request $request)
    {
        $search = $request->input('search');

        $notebooks = Notebook::with('user')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                             ->orWhereHas('user', function ($q) use ($search) {
                                 $q->where('login', 'like', "%{$search}%");
                             });
            })
            ->latest()
            ->paginate(15);

        // Путь к файлу тетрадей в админке
        return view('admin_panel.notebooks', compact('notebooks'));
    }
}
