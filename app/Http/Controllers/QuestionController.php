<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Http\Requests\QuestionRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuestionController extends Controller
{
use AuthorizesRequests;


public function __construct()
{
    $this->middleware(function ($request, $next) {
        $quiz = $request->route('quiz'); // Получаем модель Quiz из маршрута
        if ($quiz && ($quiz->user_id !== auth()->id() && !auth()->user()->isAdmin())) {
            abort(403);
        }
        return $next($request);
    });
}

   public function index(Quiz $quiz, Request $request)
{
    // Получаем все вопросы теста в порядке их создания
    $questions = $quiz->questions()->orderBy('id', 'asc')->get();

    // Добавляем виртуальный порядковый номер
    foreach ($questions as $index => $question) {
        $question->position = $index + 1;
    }

    // Разрешённые для сортировки поля
    $allowedSorts = ['position', 'question_text', 'question_points'];
    $sort = $request->input('sort', 'position'); // По умолчанию сортируем по порядку
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'position';
    }
    $order = $request->input('order', 'asc');

    // Сортировка
    if ($sort === 'position') {
        $sortedQuestions = $order === 'asc'
            ? $questions->sortBy('position')
            : $questions->sortByDesc('position');
    } else {
        $sortedQuestions = $questions->sortBy([
            [$sort, $order === 'asc' ? 'asc' : 'desc']
        ]);
    }

    // Пагинация
    $perPage = 10;
    $currentPage = request()->get('page', 1);
    $paginatedQuestions = new \Illuminate\Pagination\LengthAwarePaginator(
        $sortedQuestions->slice(($currentPage - 1) * $perPage, $perPage)->values(),
        $sortedQuestions->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    return view('quizzes.questions.index', [
        'quiz' => $quiz,
        'questions' => $paginatedQuestions
    ]);
}



    public function create(Quiz $quiz)
    {
        return view('quizzes.questions.create', compact('quiz'));
    }

    public function store(QuestionRequest $request, Quiz $quiz)
    {
        // Handle the image upload if there is one
        $questionImagePath = null;
        if ($request->hasFile('question_image') && $request->file('question_image')->isValid()) {
            $path = $request->file('question_image')->store('images', 'public');
            $questionImagePath = $path;
        }

        // Create the question
        $question = $quiz->questions()->create([
            'question_text' => $request->input('question_text'),
            'question_description' => $request->input('question_description'),
            'question_points' => $request->input('question_points'),
            'question_type' => $request->input('question_type'),
            'question_image' => $questionImagePath,
        ]);

        // Save the options
        foreach ($request->input('options') as $optionData) {
            $question->options()->create([
                'option_text' => $optionData['option_text'],
                'is_correct' => $optionData['is_correct'], // Здесь уже будет boolean
            ]);
        }

        return redirect()->route('questions.index', $quiz->id)
            ->with('success', 'Вопрос добавлен!');
    }

public function edit(Quiz $quiz, Question $question)
{
    return view('quizzes.questions.edit', compact('quiz', 'question'));
}

public function update(QuestionRequest $request, Quiz $quiz, Question $question)
{
// Обработка изображения
    $questionImagePath = $question->question_image;

    if ($request->input('remove_image') == '1') {
        if ($questionImagePath) {
            Storage::disk('public')->delete($questionImagePath);
        }
        $questionImagePath = null;
    } elseif ($request->hasFile('question_image') && $request->file('question_image')->isValid()) {
        if ($questionImagePath) {
            Storage::disk('public')->delete($questionImagePath);
        }
        $path = $request->file('question_image')->store('images', 'public');
        $questionImagePath = $path;
    }

    // Обновление вопроса
    $question->update([
        'question_text' => $request->input('question_text'),
        'question_description' => $request->input('question_description'),
        'question_points' => $request->input('question_points'),
        'question_image' => $questionImagePath,
    ]);

    // Обновление или создание вариантов ответов
    $existingOptions = $question->options;
    foreach ($request->input('options') as $index => $optionData) {
        if (isset($existingOptions[$index])) {
            $existingOptions[$index]->update([
                'option_text' => $optionData['option_text'],
                'is_correct' => filter_var($optionData['is_correct'], FILTER_VALIDATE_BOOLEAN),
            ]);
        } else {
            $question->options()->create([
                'option_text' => $optionData['option_text'],
                'is_correct' => filter_var($optionData['is_correct'], FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    // Удаление лишних вариантов ответов
    $extraOptions = array_slice($existingOptions->toArray(), count($request->input('options')));
    foreach ($extraOptions as $extraOption) {
        Option::find($extraOption['id'])->delete();
    }

    return redirect()->route('questions.index', $quiz->id)->with('success', 'Вопрос обновлен!');
}


public function deleteSelected(Request $request, Quiz $quiz)
{
    $request->validate([
        'selected' => 'required|array',  // массив ID выбранных вопросов
        'selected.*' => 'exists:questions,id,quiz_id,' . $quiz->id  // проверка, чтобы все ID существовали в таблице questions и принадлежали тесту
    ]);

    // Удаляем выбранные вопросы
    $quiz->questions()->whereIn('id', $request->input('selected'))->delete();

    // Перенаправляем с сообщением об успехе
    return back()->with('success', 'Выбранные вопросы успешно удалены.');
}




// Удаление всех вопросов
public function deleteAll(Quiz $quiz)
{

    // Удаляем все вопросы для данного теста
    $quiz->questions()->delete();

    return redirect()->route('questions.index', $quiz->id)
        ->with('success', 'Все вопросы были удалены!');
}



}