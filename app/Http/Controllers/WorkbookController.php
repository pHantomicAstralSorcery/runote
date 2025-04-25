<?php

namespace App\Http\Controllers;

use App\Models\Workbook;
use App\Models\Version;
use Illuminate\Http\Request;

class WorkbookController extends Controller
{
    public function index()
    {
        $workbooks = auth()->user()->workbooks()->latest()->get();
        return view('workbooks.index', compact('workbooks'));
    }

    public function create()
    {
        return view('workbooks.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'settings' => 'nullable|json',
            // 'content' убрали из валидации: оно приходит только на update
        ]);

        $data['user_id']  = auth()->id();
        $data['content']  = $request->input('content', '');  // ← гарантируем ключ content

        $workbook = Workbook::create($data);
        $workbook->versions()->create(['content' => $data['content']]);

        return redirect()->route('workbooks.edit', $workbook)
                         ->with('success', 'Тетрадь создана');
    }

    public function show(Workbook $workbook)
    {
        $this->authorize('view', $workbook);
        return view('workbooks.show', compact('workbook'));
    }

    public function edit(Workbook $workbook)
    {
        $this->authorize('update', $workbook);
        return view('workbooks.edit', compact('workbook'));
    }

    public function update(Request $request, Workbook $workbook)
    {
        $this->authorize('update', $workbook);

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'settings' => 'nullable|json',
            'content'  => 'nullable|string',
        ]);

        $workbook->update($data);
        $workbook->versions()->create(['content' => $data['content']]);

        $extra = $workbook->versions()->count() - 50;
        if ($extra > 0) {
            $workbook->versions()->oldest()->limit($extra)->delete();
        }

        return back()->with('success', 'Тетрадь обновлена');
    }

    public function destroy(Workbook $workbook)
    {
        $this->authorize('delete', $workbook);
        $workbook->delete();

        return redirect()->route('workbooks.index')
                         ->with('success', 'Тетрадь удалена');
    }

    public function revert(Workbook $workbook, Version $version)
    {
        $this->authorize('update', $workbook);

        $workbook->content = $version->content;
        $workbook->save();

        return redirect()->route('workbooks.edit', $workbook)
                         ->with('success', 'Откат выполнен');
    }
}
