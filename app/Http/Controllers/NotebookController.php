<?php

namespace App\Http\Controllers;

use App\Models\Notebook;
use Illuminate\Http\Request;

class NotebookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Список всех тетрадей.
     */
    public function index()
    {
        $notebooks = Notebook::paginate(10);
        return view('notebooks.index', compact('notebooks'));
    }

    /**
     * Форма создания тетради.
     */
    public function create()
    {
        return view('notebooks.create');
    }

    /**
     * Сохранение новой тетради.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'  => 'required|string|max:255',
            'access' => 'required|in:open,closed',
        ]);

        $notebook = Notebook::create($data);

        return redirect()
            ->route('notebooks.index')
            ->with('status', 'Тетрадь создана');
    }

    /**
     * Показ конкретной тетради (редактирование).
     */
    public function show(Notebook $notebook)
    {
        // Для простоты переадресуем на edit
        return redirect()->route('notebooks.edit', $notebook);
    }

    /**
     * Форма редактирования тетради.
     */
    public function edit(Notebook $notebook)
    {
        return view('notebooks.edit', compact('notebook'));
    }

    /**
     * Обновление тетради.
     */
    public function update(Request $request, Notebook $notebook)
    {
        $data = $request->validate([
            'title'  => 'required|string|max:255',
            'access' => 'required|in:open,closed',
        ]);

        $notebook->update($data);

        return redirect()
            ->route('notebooks.index')
            ->with('status', 'Тетрадь обновлена');
    }

    /**
     * Удаление тетради.
     */
    public function destroy(Notebook $notebook)
    {
        $notebook->delete();
        return redirect()
            ->route('notebooks.index')
            ->with('status', 'Тетрадь удалена');
    }

public function saveContent(Request $request, Notebook $notebook)
{
    $data = $request->validate([
        'content' => 'nullable|string',
    ]);

    $notebook->update(['content' => $data['content']]);

    return response()->json(['status' => 'ok']);
}

}
