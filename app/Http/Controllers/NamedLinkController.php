<?php

namespace App\Http\Controllers;

use App\Models\Workbook;
use App\Models\NamedLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NamedLinkController extends Controller
{
    public function index(Workbook $workbook)
    {
        $this->authorize('view', $workbook);
        $links = $workbook->namedLinks;
        return view('workbooks.links.index', compact('workbook', 'links'));
    }

    public function store(Request $request, Workbook $workbook)
    {
        $this->authorize('update', $workbook);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workbook->namedLinks()->create([
            'name'     => $data['name'],
            'slug'     => Str::lower(Str::random(8)),
            'active'   => true,
            'open_at'  => null,
            'close_at' => null,
        ]);

        return back()->with('success', 'Ссылка создана');
    }

    public function destroy(Workbook $workbook, NamedLink $link)
    {
        $this->authorize('update', $workbook);
        $link->delete();
        return back()->with('success', 'Ссылка удалена');
    }

    public function show($slug)
    {
        $link = NamedLink::with('workbook.fields', 'responses')
                ->where('slug', $slug)
                ->firstOrFail();

        abort_if(
            !$link->active
            || ($link->open_at && now()->lt($link->open_at))
            || ($link->close_at && now()->gt($link->close_at)),
            403
        );

        return view('links.fill', compact('link'));
    }
}
