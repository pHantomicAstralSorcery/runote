<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\Notebook; // Добавляем модель Notebook
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Для проверки владельца

class DraftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Только авторизованные пользователи могут управлять черновиками
    }

    /**
     * Store a new draft for a notebook.
     * Сохраняет новый черновик для тетради.
     */
    public function store(Request $request, Notebook $notebook)
    {
        // Проверяем, что текущий пользователь является владельцем тетради
        if (Auth::id() !== $notebook->user_id) {
            return response()->json(['message' => 'Доступ запрещен.'], 403);
        }

        $data = $request->validate([
            'content_data' => 'required|array', // JSON-данные черновика
        ]);

        // Находим или создаем черновик для текущего пользователя и тетради
        // Здесь мы могли бы использовать 'notebook_id' и 'author_id' как уникальные ключи
        // Если черновик для этой тетради и автора уже существует, мы его обновляем.
        // Иначе создаем новый.
        $draft = Draft::updateOrCreate(
            [
                'notebook_id' => $notebook->id,
                'author_id'   => Auth::id(),
            ],
            [
                'data'        => $data['content_data'],
                'created_at'  => now(), // Обновляем время создания, если это updateOrCreate
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Черновик сохранен.', 'draft_id' => $draft->id]);
    }

    /**
     * Display the specified draft.
     * Отображает содержимое черновика.
     */
    public function show(Notebook $notebook)
    {
        // Проверяем, что текущий пользователь является владельцем тетради
        if (Auth::id() !== $notebook->user_id) {
            return response()->json(['message' => 'Доступ запрещен.'], 403);
        }

        $draft = Draft::where('notebook_id', $notebook->id)
                      ->where('author_id', Auth::id())
                      ->first();

        if (!$draft) {
            return response()->json(['status' => 'error', 'message' => 'Черновик не найден.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $draft->data]);
    }

    /**
     * Remove the specified draft from storage.
     * Удаляет черновик.
     */
    public function destroy(Notebook $notebook)
    {
        // Проверяем, что текущий пользователь является владельцем тетради
        if (Auth::id() !== $notebook->user_id) {
            return response()->json(['message' => 'Доступ запрещен.'], 403);
        }

        $deleted = Draft::where('notebook_id', $notebook->id)
                        ->where('author_id', Auth::id())
                        ->delete();

        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'Черновик удален.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Черновик не найден или не удалось удалить.'], 404);
    }

    // Методы index, create, edit, update не нужны для прямого взаимодействия с пользователем через Resource
    public function index() { abort(404); }
    public function create() { abort(404); }
    public function edit(Draft $draft) { abort(404); }
    public function update(Request $request, Draft $draft) { abort(404); }
}
