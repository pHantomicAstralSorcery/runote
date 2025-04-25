<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller
{
use AuthorizesRequests;
public function show(Document $document)
    {
        // Можно добавить авторизацию: $this->authorize('update', $document);
        return view('documents.show', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $data = $request->validate([
            'content' => 'required',
            'delta'   => 'required',
        ]);

        // Авторизация: проверяем, имеет ли пользователь право редактировать документ
        $this->authorize('update', $document);

        // Обновляем документ (сохраняем в виде JSON)
        $document->update([
            'content' => $data['content']
        ]);

        // Сохраняем версию документа
        \App\Models\DocumentVersion::create([
            'document_id' => $document->id,
            'content'     => $data['content'],
            'version_hash'=> md5($data['content']),
        ]);

        return response()->json(['status' => 'success']);
    }
}
