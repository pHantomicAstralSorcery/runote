<?php

namespace App\Http\Controllers;

use App\Models\NamedLink;
use App\Models\Notebook;
use App\Models\StudentResponse;
use App\Models\StudentNotebookInstance;
use App\Models\ResponseField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NamedLinkController extends Controller
{
    /**
     * Display a listing of the named links for a specific notebook.
     */
    public function index(Notebook $notebook)
    {
        try {
            $this->authorize('view', $notebook);

            $cacheKey = 'notebook_named_links_' . $notebook->id . '_' . Auth::id();
            
            $links = Cache::remember($cacheKey, 60 * 5, function () use ($notebook) {
                return $notebook->namedLinks()->with([
                    'studentInstance' => function ($query) {
                        $query->withCount('studentResponses');
                    },
                    'notebook.currentSnapshot.responseFields'
                ])->latest()->get();
            });

            $links->each(function ($link) {
                if ($link->studentInstance) {
                    $totalFields = $link->notebook->currentSnapshot ? $link->notebook->currentSnapshot->responseFields->count() : 0;
                    $answeredFields = $link->studentInstance->student_responses_count; 
                    
                    $link->studentInstance->is_completed = ($totalFields > 0 && $answeredFields >= $totalFields);
                }
            });

            return response()->json($links);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => 'Доступ запрещен: ' . $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Ошибка при загрузке ссылок: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created named link in storage.
     */
    public function store(Request $request, Notebook $notebook)
    {
        $this->authorize('update', $notebook); 
        
        try {
            if ($notebook->access === 'closed') {
                return response()->json(['message' => 'Нельзя создавать ссылки для тетради с закрытым доступом.'], 403);
            }
            if (!$notebook->currentSnapshot) {
                return response()->json(['message' => 'Для создания ссылки необходимо сначала сохранить тетрадь.'], 400);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'is_active' => 'boolean',
            ]);

            $link = $notebook->namedLinks()->create([
                'token'       => Str::random(32),
                'title'       => $data['title'],
                'is_active'   => $data['is_active'] ?? true,
            ]);

            StudentNotebookInstance::create([
                'named_link_id'      => $link->id,
                'notebook_snapshot_id' => $notebook->currentSnapshot->id,
                'user_id' => null,
            ]);

            Cache::forget('notebook_named_links_' . $notebook->id . '_' . Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => "Ссылка «{$link->title}» создана!",
                'link' => $link->load('studentInstance'),
            ], 201);

        } catch (Throwable $e) {
            $errorMessage = 'Ошибка при создании ссылки: ' . $e->getMessage();
            return response()->json(['message' => $errorMessage], 500);
        }
    }

    /**
     * Update the specified named link in storage.
     */
    public function update(Request $request, Notebook $notebook, NamedLink $namedLink)
    {
        try {
            $this->authorize('update', $notebook);
            if ($namedLink->notebook_id !== $notebook->id) {
                return response()->json(['message' => 'Доступ запрещен.'], 403);
            }

            $data = $request->validate([
                'title'     => 'required|string|max:255',
                'is_active' => 'boolean',
            ]);

            $namedLink->update([
                'title'     => $data['title'],
                'is_active' => $data['is_active'] ?? $namedLink->is_active,
            ]);

            Cache::forget('notebook_named_links_' . $notebook->id . '_' . Auth::id());

            return response()->json(['status' => 'success', 'message' => "Ссылка «{$namedLink->title}» обновлена."]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Ошибка при обновлении ссылки: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified named link from storage.
     */
    public function destroy(Notebook $notebook, NamedLink $namedLink)
    {
        try {
            $this->authorize('update', $notebook);
             if ($namedLink->notebook_id !== $notebook->id) {
                return response()->json(['message' => 'Доступ запрещен.'], 403);
            }
            
            $namedLink->delete();

            Cache::forget('notebook_named_links_' . $notebook->id . '_' . Auth::id());

            return response()->json(['status' => 'success', 'message' => 'Ссылка успешно удалена.']);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Ошибка при удалении ссылки: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * View the student's copy of the notebook via token (public access).
     */
    public function view(string $token)
    {
        try {
            $link = NamedLink::where('token', $token)
                            ->with(['notebook.user', 'studentInstance.snapshot.responseFields'])
                            ->firstOrFail();

            $isOwner = Auth::check() && Auth::id() === $link->notebook->user_id;

            // Если ссылка неактивна или тетрадь закрыта И текущий пользователь НЕ является владельцем,
            // перенаправляем на страницу blocked.
            if (!$isOwner && ($link->notebook->access === 'closed' || !$link->is_active)) {
                return redirect()->route('blocked'); // Перенаправляем на именованный маршрут 'blocked'
            }
            
            $studentInstance = $link->studentInstance;
            $notebook = $link->notebook;

            if (!$studentInstance || ($notebook && $notebook->currentSnapshot && $studentInstance->notebook_snapshot_id !== $notebook->current_snapshot_id)) {
                if ($studentInstance) {
                    $studentInstance->update(['notebook_snapshot_id' => $notebook->current_snapshot_id]);
                } else {
                    $studentInstance = StudentNotebookInstance::create([
                        'named_link_id'      => $link->id,
                        'notebook_snapshot_id' => $notebook->currentSnapshot->id,
                        'user_id' => null,
                    ]);
                }
            }

            $studentInstance->load('studentResponses');

            $studentInstance->update(['last_accessed_at' => now()]);
            
            return view('named_links.view', compact('link', 'studentInstance', 'notebook'));
        } catch (Throwable $e) {
            // Если ссылка не найдена или произошла другая ошибка, также можно перенаправить на 'blocked'
            // или другую страницу ошибки, в зависимости от желаемого поведения.
            // Сейчас оставляем возврат view('errors.custom-error') как было, но можно изменить на redirect.
            \Log::error('Ошибка при загрузке тетради по ссылке: ' . $e->getMessage(), ['token' => $token, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('blocked'); // Перенаправляем на страницу блокировки в случае ошибки
        }
    }

    /**
     * Receive and auto-save student responses.
     */
    public function submit(Request $request, string $token)
    {
        try {
            $namedLink = NamedLink::where('token', $token)->firstOrFail();

            if ($namedLink->notebook->access === 'closed' || !$namedLink->is_active) {
                return response()->json(['message' => 'Эта ссылка неактивна, ответы не могут быть сохранены.'], 403);
            }

            $studentInstance = $namedLink->studentInstance;
            
            // === FIX START ===
            if (!$studentInstance || !$studentInstance->snapshot) {
                Log::error('Attempted to submit response for an instance with no snapshot.', ['named_link_id' => $namedLink->id]);
                return response()->json(['message' => 'Экземпляр тетради или его снимок не найден. Невозможно сохранить ответы.'], 404);
            }
            // === FIX END ===
            
            $data = $request->validate([
                'responses'   => 'array',
                'responses.*.uuid' => 'required|string',
                'responses.*.input' => 'nullable',
            ]);

            $results = [];

            $currentSnapshotResponseFields = $studentInstance->snapshot->responseFields->keyBy('uuid');

            $index = 0; 
            foreach ($data['responses'] as $responseEntry) {
                $fieldUuid = $responseEntry['uuid'];
                $responseField = $currentSnapshotResponseFields->get($fieldUuid);
                
                if (!$responseField) { 
                    $index++;
                    continue; 
                } 

                $userInput = $responseEntry['input'] ?? null;
                $isCorrect = null;

                if ($responseField->field_type === 'file') {
                    $fileKey = "responses.{$index}.input";
                    if ($request->hasFile($fileKey)) { 
                        $file = $request->file($fileKey);
                        $path = $file->store('student_uploads', 'public');
                        $userInput = json_encode([
                            'path' => $path,
                            'url' => Storage::disk('public')->url($path),
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                        ]);
                    } else if (is_string($userInput) && json_decode($userInput) !== null) {
                        // Keep old file response.
                    } else {
                        $userInput = null;
                    }
                } else {
                    $correctAnswers = $responseField->correct_answers;
                    if (is_string($correctAnswers)) {
                        $correctAnswers = json_decode($correctAnswers, true); 
                    }
                    if (!is_array($correctAnswers)) {
                        $correctAnswers = [];
                    }
                    
                    if ($responseField->field_type === 'text') {
                        $expectedText = $correctAnswers['text'] ?? null;
                        if ($expectedText !== null) {
                            $isCorrect = (mb_strtolower(trim($userInput)) === mb_strtolower(trim($expectedText)));
                        }
                    } elseif ($responseField->field_type === 'select') {
                        $expectedSelect = $correctAnswers['select'] ?? null;
                        if ($expectedSelect !== null) {
                            $isCorrect = (trim($userInput) === trim($expectedSelect));
                        }
                    }
                }

                StudentResponse::updateOrCreate(
                    ['student_notebook_instance_id' => $studentInstance->id, 'response_field_uuid' => $fieldUuid],
                    ['user_input' => $userInput, 'is_correct' => $isCorrect]
                );
                
                $results[$fieldUuid] = $isCorrect;
                $index++;
            }

            return response()->json(['status' => 'success', 'results' => $results]);
        } catch (Throwable $e) {
            Log::error('Ошибка при сохранении ответов в NamedLinkController@submit: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json(['message' => 'Ошибка при сохранении ответов: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Deletes a student's response for a specific field, including any associated file.
     */
    public function deleteResponse(Request $request, string $token, string $fieldUuid)
    {
        try {
            $namedLink = NamedLink::where('token', $token)->firstOrFail();
            $studentInstance = $namedLink->studentInstance;

            if (!$studentInstance) {
                return response()->json(['message' => 'Экземпляр тетради не найден.'], 404);
            }

            $response = StudentResponse::where('student_notebook_instance_id', $studentInstance->id)
                                        ->where('response_field_uuid', $fieldUuid)
                                        ->first();

            if ($response) {
                if ($response->user_input) {
                    $fileData = json_decode($response->user_input, true);
                    if (is_array($fileData) && isset($fileData['path'])) {
                        Storage::disk('public')->delete($fileData['path']);
                    }
                }
                $response->delete();
            }

            return response()->json(['status' => 'success', 'message' => 'Ответ успешно удален.']);

        } catch (Throwable $e) {
            Log::error('Ошибка при удалении ответа: ' . $e->getMessage(), [
                'token' => $token,
                'fieldUuid' => $fieldUuid,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Ошибка сервера при удалении ответа.'], 500);
        }
    }

    /**
     * Remove all named links for a specific notebook.
     */
    public function destroyAll(Notebook $notebook)
    {
        $this->authorize('update', $notebook);
        try {
            DB::transaction(function () use ($notebook) {
                $notebook->namedLinks()->delete();
            });
            Cache::forget('notebook_named_links_' . $notebook->id . '_' . Auth::id());
            return response()->json(['status' => 'success', 'message' => 'Все ссылки были удалены.']);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Ошибка при удалении всех ссылок: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle active status for all named links of a notebook.
     */
    public function toggleAll(Request $request, Notebook $notebook)
    {
        $this->authorize('update', $notebook);
        try {
            $data = $request->validate(['action' => 'required|in:activate,deactivate']);
            
            if ($data['action'] === 'activate' && $notebook->access === 'closed') {
                 return response()->json(['message' => 'Нельзя активировать ссылки, так как тетрадь закрыта для доступа.'], 403);
            }

            $newStatus = ($data['action'] === 'activate');
            
            $notebook->namedLinks()->update(['is_active' => $newStatus]);
            
            Cache::forget('notebook_named_links_' . $notebook->id . '_' . Auth::id());
            
            $statusText = $newStatus ? 'активированы' : 'деактивированы';
            return response()->json(['status' => 'success', 'message' => "Все ссылки были {$statusText}."]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Ошибка при изменении статуса ссылок: ' . $e->getMessage()], 500);
        }
    }
}
