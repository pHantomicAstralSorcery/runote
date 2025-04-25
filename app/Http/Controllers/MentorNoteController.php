<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MentorNoteController extends Controller
{
    // Возвращает список заметок для документа
    public function index(Document $document)
    {
        // Авторизация: пользователь должен иметь доступ к документу
        $this->authorize('viewNotes', $document);

        $notes = $document->mentorNotes()->get();
        return response()->json($notes);
    }

    // Создание новой заметки (только для менторов)
    public function store(Request $request, Document $document)
    {
        // Проверяем, что пользователь является ментором для этого документа
        $this->authorize('createNote', $document);

        $data = $request->validate([
            'content' => 'required|string',
            'anchor'  => 'nullable|string',
        ]);

        $note = MentorNote::create([
            'content'     => $data['content'],
            'anchor'      => $data['anchor'] ?? null,
            'document_id' => $document->id,
            'mentor_id'   => auth()->id(),
            'mentee_id'   => $document->author_id, // логика назначения подопечного
        ]);

        return response()->json($note, 201);
    }

    // Добавление ответа подопечным (один ответ на заметку)
    public function reply(Request $request, MentorNote $mentorNote)
    {
        // Проверяем, что текущий пользователь — подопечный и ответа ещё нет
        $this->authorize('replyNote', $mentorNote);

        // Если ответ уже существует, можно вернуть ошибку
        if (!empty($mentorNote->reply)) {
            return response()->json(['error' => 'Ответ уже оставлен'], 403);
        }

        $data = $request->validate([
            'reply' => 'required|string',
        ]);

        $mentorNote->update([
            'reply' => $data['reply'],
            'resolved_at' => now(),
        ]);

        return response()->json($mentorNote);
    }
}
