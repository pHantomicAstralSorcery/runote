<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Notebook;
use Illuminate\Http\Request;
use App\Models\NotebookSnapshot;
use App\Models\ResponseField;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB; // Импортируем для транзакций
use Illuminate\Support\Facades\Cache; // Добавляем импорт Cache для полной картины

class NotebookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the notebooks.
     */
    public function index()
    {
        $notebooks = Notebook::where('user_id', Auth::id())->paginate(10);
        return view('notebooks.index', compact('notebooks'));
    }

    /**
     * Show the form for creating a new notebook.
     */
    public function create()
    {
        return view('notebooks.create');
    }

    /**
     * Store a newly created notebook in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title'  => 'required|string|max:255',
                'access' => 'required|in:open,closed',
            ]);

            $data['user_id'] = Auth::id();

            $notebook = Notebook::create($data);

            $initialContent = '<p>Добро пожаловать в <strong>редактор</strong>!</p>';

            $snapshot = NotebookSnapshot::create([
                'notebook_id'    => $notebook->id,
                'version_number' => 1,
                'content_html'   => $initialContent,
            ]);

            $notebook->current_snapshot_id = $snapshot->id;
            $notebook->save();

            return redirect()
                ->route('notebooks.edit', $notebook)
                ->with('status', 'Тетрадь успешно создана!');
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['creationError' => 'Ошибка при создании тетради: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified notebook (redirect to edit).
     */
    public function show(Notebook $notebook)
    {
        return redirect()->route('notebooks.edit', $notebook);
    }

    /**
     * Show the form for editing the specified notebook.
     */
    public function edit(Notebook $notebook)
    {
        $this->authorize('update', $notebook);

        if (!$notebook->currentSnapshot) {
            try {
                $initialContent = '<p>Добро пожаловать в <strong>редактор</strong>!</p>';
                $snapshot = NotebookSnapshot::create([
                    'notebook_id'    => $notebook->id,
                    'version_number' => 1,
                    'content_html'   => $initialContent,
                ]);
                $notebook->current_snapshot_id = $snapshot->id;
                $notebook->save();
                $notebook->load('currentSnapshot');
            } catch (Throwable $e) {
                return redirect()->route('notebooks.index')->with('error', 'Не удалось инициализировать тетрадь: ' . $e->getMessage());
            }
        }

        return view('notebooks.edit', compact('notebook'));
    }

    /**
     * Update the specified notebook in storage (general settings only).
     */
    public function update(Request $request, Notebook $notebook)
    {
        try {
            $this->authorize('update', $notebook);

            $data = $request->validate([
                'title'  => 'required|string|max:255',
                'access' => 'required|in:open,closed',
            ]);
            
            $notebook->update($data);
            
            // Если доступ к тетради закрывается, деактивируем все связанные именные ссылки
            if ($data['access'] === 'closed') {
                $notebook->namedLinks()->update(['is_active' => false]);
            }

            return response()->json(['status' => 'success', 'message' => 'Настройки тетради обновлены!', 'title' => $notebook->title]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Ошибка при обновлении настроек: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified notebook from storage.
     */
    public function destroy(Notebook $notebook)
    {
        try {
            $this->authorize('delete', $notebook);
            $notebook->delete();
            return redirect()
                ->route('notebooks.index')
                ->with('status', 'Тетрадь успешно удалена.');
        } catch (Throwable $e) {
            return back()->with('error', 'Ошибка при удалении тетради: ' . $e->getMessage());
        }
    }

    /**
     * Save a snapshot of the notebook content.
     */
    public function saveSnapshot(Request $request, Notebook $notebook)
    {
        // Обертываем всю логику в транзакцию для обеспечения целостности данных
        DB::beginTransaction();
        try {
            $this->authorize('update', $notebook);

            $data = $request->validate([
                'content_html' => 'nullable|string',
                'response_fields' => 'nullable|array',
                'response_fields.*.uuid' => 'required|string',
                'response_fields.*.field_type' => 'required|string',
                'response_fields.*.label' => 'nullable|string',
                'response_fields.*.order' => 'required|integer',
                'response_fields.*.validation_rules' => 'nullable|array',
                'response_fields.*.correct_answers' => 'nullable|array',
            ]);

            $nextVersionNumber = ($notebook->snapshots()->max('version_number') ?? 0) + 1;

            $snapshot = NotebookSnapshot::create([
                'notebook_id'    => $notebook->id,
                'version_number' => $nextVersionNumber,
                'content_html'   => $data['content_html'] ?? '',
            ]);

            // ИСПРАВЛЕНИЕ: Ошибка UNIQUE constraint failed.
            // Теперь мы просто создаем новые поля для каждого нового снимка.
            // Это предполагает, что в таблице `response_fields` нет ГЛОБАЛЬНОГО уникального
            // индекса на `uuid`, а есть, например, композитный: `UNIQUE(notebook_snapshot_id, uuid)`.
            if (!empty($data['response_fields'])) {
                foreach ($data['response_fields'] as $fieldData) {
                    $snapshot->responseFields()->create($fieldData);
                }
            }

            $notebook->current_snapshot_id = $snapshot->id;
            $notebook->save();
            
            DB::commit(); // Завершаем транзакцию

            return response()->json(['status' => 'ok', 'message' => 'Тетрадь успешно сохранена как новая версия!']);
        } catch (Throwable $e) {
            DB::rollBack(); // Откатываем транзакцию в случае ошибки
            return response()->json(['message' => 'Ошибка при сохранении снимка: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get a list of snapshots for a notebook.
     */
    public function getSnapshots(Notebook $notebook)
    {
        $this->authorize('view', $notebook);
        $snapshots = $notebook->snapshots()->orderByDesc('created_at')->get();
        return response()->json($snapshots);
    }

    /**
     * Get the HTML content of a specific snapshot.
     * Updated method signature for Implicit Model Binding with parent model.
     */
    public function getSnapshotContent(Notebook $notebook, NotebookSnapshot $notebookSnapshot) // <-- ИЗМЕНЕНИЕ ЗДЕСЬ
    {
        // Дополнительная проверка, чтобы убедиться, что снимок принадлежит этой тетради
        if ($notebookSnapshot->notebook_id !== $notebook->id) {
            abort(404, 'Снимок не принадлежит указанной тетради.');
        }

        $this->authorize('view', $notebookSnapshot->notebook);
        return response()->json(['content_html' => $notebookSnapshot->content_html]);
    }

    /**
     * Revert the notebook to a previous snapshot.
     */
    public function revertSnapshot(Request $request, Notebook $notebook, NotebookSnapshot $notebookSnapshot)
    {
        DB::beginTransaction();
        try {
            $this->authorize('update', $notebook);

            if ($notebookSnapshot->notebook_id !== $notebook->id) {
                throw new AuthorizationException('Снимок не принадлежит этой тетради.');
            }
            
            // Удаляем все снимки, которые были созданы ПОСЛЕ выбранного для отката.
            $notebook->snapshots()
                     ->where('created_at', '>', $notebookSnapshot->created_at)
                     ->orWhere(function ($query) use ($notebookSnapshot) {
                         $query->where('created_at', $notebookSnapshot->created_at)
                               ->where('id', '>', $notebookSnapshot->id);
                     })
                     ->delete();

            $nextVersionNumber = ($notebook->snapshots()->max('version_number') ?? 0) + 1;

            $newSnapshot = NotebookSnapshot::create([
                'notebook_id'    => $notebook->id,
                'version_number' => $nextVersionNumber,
                'content_html'   => $notebookSnapshot->content_html ?? '',
            ]);

            if ($notebookSnapshot->responseFields->isNotEmpty()) {
                foreach ($notebookSnapshot->responseFields as $responseField) {
                    $fieldData = Arr::except($responseField->toArray(), ['id', 'notebook_snapshot_id', 'created_at', 'updated_at']);
                    $newSnapshot->responseFields()->create($fieldData);
                }
            }

            $notebook->current_snapshot_id = $newSnapshot->id;
            $notebook->save();
            
            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Тетрадь успешно откачена к выбранной версии.']);

        } catch (AuthorizationException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 403);
        }
        catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ошибка при откате тетради: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Get the access status of a specific notebook.
     * Used by frontend to determine if links should be active.
     */
    public function accessStatus(Notebook $notebook)
    {
        try {
            // Проверяем, что текущий пользователь может просматривать эту тетрадь.
            $this->authorize('view', $notebook);

            // Возвращаем только статус доступа тетради
            return response()->json(['access' => $notebook->access]);
        } catch (AuthorizationException $e) {
            // Если авторизация не пройдена, возвращаем 403.
            return response()->json(['message' => 'Доступ запрещен: ' . $e->getMessage()], 403);
        } catch (Throwable $e) {
            // В случае любой другой ошибки
            return response()->json(['message' => 'Ошибка при получении статуса доступа тетради: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display overall statistics for all named links of a notebook.
     */
    public function showOverallStatistics(Notebook $notebook)
    {
        // Проверяем права доступа
        $this->authorize('view', $notebook);

        // Загружаем все необходимые связи для расчета статистики
        $notebook->load([
            'namedLinks.studentInstance' => function ($query) {
                $query->withCount([
                    'studentResponses as answered_responses_count' => function ($query) {
                        // Считаем только те ответы, которые не null (т.е. на них был дан ответ)
                        $query->whereNotNull('user_input');
                    },
                    'correctResponses', // Правильных ответов
                    'incorrectResponses' => function ($query) {
                        // Подсчитываем только неправильные ответы (is_correct = false), исключая пустые
                        $query->where('is_correct', false)->whereNotNull('user_input');
                    }
                ])->with('snapshot.responseFields'); // Поля из снимка экземпляра
            }
        ]);

        $linksData = [];
        $totalFieldsOverall = 0;
        $totalAnsweredFieldsOverall = 0; // Общее количество отвеченных полей
        $totalCorrectAnswersOverall = 0;
        $totalIncorrectAnswersOverall = 0; // Общее количество неправильных ответов (исключая пустые)
        $completedLinksCount = 0;

        foreach ($notebook->namedLinks as $link) {
            $instance = $link->studentInstance;
            if (!$instance) continue;

            $totalFields = $instance->snapshot->responseFields->count();
            $answeredResponses = $instance->answered_responses_count; // Количество отвеченных полей
            $correctAnswers = $instance->correct_responses_count;
            $incorrectAnswers = $instance->incorrect_responses_count; // Количество неправильных ответов (исключая пустые)

            // Процент выполнения для конкретной ссылки (на основе отвеченных полей)
            $completionPercent = ($totalFields > 0) ? round(($answeredResponses / $totalFields) * 100) : 0;
            
            if ($completionPercent >= 100) {
                $completedLinksCount++;
            }

            // Агрегируем общие данные
            $totalFieldsOverall += $totalFields;
            $totalAnsweredFieldsOverall += $answeredResponses;
            $totalCorrectAnswersOverall += $correctAnswers;
            $totalIncorrectAnswersOverall += $incorrectAnswers;

            $linksData[] = [
                'link' => $link,
                'instance' => $instance,
                'stats' => [
                    'totalFields' => $totalFields,
                    'answeredResponses' => $answeredResponses, // Добавляем в stats
                    'correctAnswers' => $correctAnswers,
                    'incorrectAnswers' => $incorrectAnswers,
                    'completionPercent' => $completionPercent,
                ]
            ];
        }

        // Вычисляем общую статистику
        // Общий процент выполнения на основе всех отвеченных полей
        $overallCompletionPercent = ($totalFieldsOverall > 0) ? round(($totalAnsweredFieldsOverall / $totalFieldsOverall) * 100) : 0;
        $totalLinksCount = $notebook->namedLinks->count();

        $overallStats = [
            'totalLinks' => $totalLinksCount,
            'completedLinks' => $completedLinksCount,
            'uncompletedLinks' => $totalLinksCount - $completedLinksCount,
            'overallCompletionPercent' => $overallCompletionPercent,
            'totalCorrectAnswers' => $totalCorrectAnswersOverall,
            'totalIncorrectAnswers' => $totalIncorrectAnswersOverall, // Теперь это только неправильные, без учета пустых
            'totalUnanswered' => $totalFieldsOverall - $totalAnsweredFieldsOverall, // Добавляем общее количество неотвеченных полей
            'totalFieldsOverall' => $totalFieldsOverall, // <--- ДОБАВЛЕНО: Теперь totalFieldsOverall будет доступен в Blade
        ];

        return view('notebooks.overall_statistics', compact('notebook', 'linksData', 'overallStats'));
    }
}
