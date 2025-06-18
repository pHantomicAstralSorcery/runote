<?php
namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizUserAnswer;
use App\Http\Requests\QuizRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuizController extends Controller
{
use AuthorizesRequests;

public function __construct()
{
    $this->authorizeResource(Quiz::class, 'quiz');
    // Применяем политику 'statistics' для метода statistics
    $this->middleware('can:statistics,quiz')->only('statistics');
    // Применяем политику 'publish' для метода publish
    $this->middleware('can:publish,quiz')->only('publish');
    // Применяем политику 'resultsDetails' для метода resultsDetails
    $this->middleware('can:resultsDetails,quiz')->only('resultsDetails');
}

    

 public function index(Request $request)
{
    $quizzesQuery = Quiz::where('is_published', true)->where('access_type', 'open');

    // Поиск по названию и автору
    if ($request->has('search') && $request->input('search')) {
        $search = $request->input('search');
        $quizzesQuery->where(function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('login', 'like', '%' . $search . '%');
                });
        });
    }

    // Сортировка
    $sort = $request->input('sort', 'created_at'); // По умолчанию сортируем по дате создания
    $order = $request->input('order', 'desc'); // По умолчанию убывающий порядок

    $validSortFields = ['title', 'user.login', 'time_limit', 'attempt_limit', 'created_at'];
    if (in_array($sort, $validSortFields)) {
        if ($sort === 'user.login') {
            $quizzesQuery->join('users', 'quizzes.user_id', '=', 'users.id')
                ->orderBy('users.login', $order);
        } else {
            $quizzesQuery->orderBy($sort, $order);
        }
    } else {
        $quizzesQuery->orderBy('created_at', 'desc');
    }

    $quizzes = $quizzesQuery->paginate(20)->appends($request->query());

    return view('quizzes.index', compact('quizzes'));
}


    public function create()
    {
        return view('quizzes.create');
    }

    public function store(QuizRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $quiz = Quiz::create($validated);
        $quiz->updatePublishedStatus();
        return redirect()->route('questions.create', $quiz->id);
    }

    public function show(Quiz $quiz)
    {
        $this->authorize('view', $quiz);
        $user = Auth::user();

        if ($quiz->attempt_limit_type === 'custom') {
            $completedAttemptsCount = $quiz->users()
                ->where('users.id', $user->id)
                ->wherePivot('status', 'completed')
                ->count();

            if ($completedAttemptsCount >= $quiz->attempt_limit) {
                return redirect()->route('quizzes.index')
                    ->with('error', 'У вас закончились попытки для прохождения этого теста.');
            }
        }
        return view('quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz)
    {
        return view('quizzes.edit', compact('quiz'));
    }

    public function update(QuizRequest $request, Quiz $quiz)
    {
        $validated = $request->validated();

        $quiz->update($validated);
        $quiz->updatePublishedStatus();

        return redirect()->route('quizzes.myQuizzes', $quiz->id);
    }


    public function take(Quiz $quiz, $questionIndex = 0)
    {
        $this->authorize('view', $quiz);
        $user = Auth::user();

        if ($quiz->attempt_limit_type === 'custom') {
            $completedAttemptsCount = $quiz->users()
                ->where('users.id', $user->id)
                ->wherePivot('status', 'completed')
                ->count();

            if ($completedAttemptsCount >= $quiz->attempt_limit) {
                return redirect()->route('quizzes.index')
                    ->with('error', 'У вас закончились попытки для прохождения этого теста.');
            }
        }

        $questions = $quiz->questions;
        $totalQuestions = $questions->count();

        if ($totalQuestions == 0) {
            return view('quizzes.no_questions', compact('quiz'));
        }
        if ($questionIndex < 0) {
            $questionIndex = 0;
        } elseif ($questionIndex >= $totalQuestions) {
            $questionIndex = $totalQuestions - 1;
        }

        $currentQuestion = $questions[$questionIndex];
        $quizUser = $quiz->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'in_progress')
            ->first();

        if (!$quizUser) {
            $attemptNumber = $quiz->users()->where('users.id', $user->id)->max('attempt_number') + 1;

            $quiz->users()->attach($user->id, [
                'score'          => 0,
                'attempt_number' => $attemptNumber,
                'started_at'     => now(),
                'status'         => 'in_progress'
            ]);
            $quizUser = $quiz->users()
                ->where('users.id', $user->id)
                ->wherePivot('status', 'in_progress')
                ->first();
        }

        $progress = (($questionIndex + 1) / $totalQuestions) * 100;

        return view('quizzes.take', [
            'quiz'             => $quiz,
            'currentQuestion'  => $currentQuestion,
            'questionIndex'    => $questionIndex,
            'totalQuestions'   => $totalQuestions,
            'progress'         => $progress,
            'attemptNumber'    => $quizUser->pivot->attempt_number,
            'maxAttempts'      => $quiz->attempt_limit,
            'timeRemaining'    => $this->calculateTimeRemaining($quiz)
        ]);
    }


    private function calculateTimeRemaining(Quiz $quiz)
    {
        $user = Auth::user();
        $quizUser = $quiz->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'in_progress')
            ->first();
        if (!$quizUser) {
            return 0;
        }
        $timeLimitInMinutes = $quiz->time_limit;  
        if (!$timeLimitInMinutes) return 0;
        $timeLimitInSeconds = $timeLimitInMinutes * 60;
        
        $startTime = Carbon::parse($quizUser->pivot->started_at);
        $endTime = $startTime->copy()->addSeconds($timeLimitInSeconds);
        $remaining = $endTime->getTimestamp() - now()->getTimestamp();
        return $remaining > 0 ? $remaining : 0;
    }


    public function submitAnswer(Request $request, Quiz $quiz, $questionIndex)
    {
        $this->authorize('view', $quiz);
        $user = Auth::user();
        $question = $quiz->questions[$questionIndex];
        $quizUser = $quiz->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'in_progress')
            ->first();

        if (!$quizUser) {
            $quiz->users()->attach($user->id, [
                'score'          => 0,
                'started_at'     => now(),
                'status'         => 'in_progress',
                'attempt_number' => $quiz->users()->where('users.id', $user->id)->max('attempt_number') + 1
            ]);

            $quizUser = $quiz->users()
                ->where('users.id', $user->id)
                ->wherePivot('status', 'in_progress')
                ->first();
        }
        $answerInput = $request->input('answer');

        if (is_null($answerInput) || (is_array($answerInput) && empty($answerInput))) {
            return redirect()->route('quizzes.take', ['quiz' => $quiz->id, 'questionIndex' => $questionIndex])
                ->with('error', 'Вы не выбрали ответ.');
        }

        $newIsCorrect = false;
        if ($question->question_type === 'single' || $question->question_type === 'multiple') {
            $userAnswerArray = (array) $answerInput;
            $correctAnswers = $question->options->where('is_correct', true)->pluck('id')->sort()->toArray();
            $userAnswerArray = collect($userAnswerArray)->sort()->toArray();
            $newIsCorrect = empty(array_diff($userAnswerArray, $correctAnswers));
        } elseif ($question->question_type === 'text') {
            $correctAnswer = $question->options->where('is_correct', true)->first();
            $newIsCorrect = (strtolower(trim($answerInput)) === strtolower(trim($correctAnswer->option_text)));
        }

        $newAnswerValue = is_array($answerInput) ? json_encode($answerInput) : $answerInput;
        $existingAnswer = QuizUserAnswer::where('quiz_user_id', $quizUser->pivot->id)
            ->where('question_id', $question->id)
            ->first();

        if ($existingAnswer) {
            if ($existingAnswer->answer !== $newAnswerValue) {
                $oldIsCorrect = $existingAnswer->is_correct;

                $existingAnswer->answer = $newAnswerValue;
                $existingAnswer->is_correct = $newIsCorrect;
                $existingAnswer->save();
                $delta = 0;
                if ($oldIsCorrect && !$newIsCorrect) {
                    $delta = -$question->question_points;
                } elseif (!$oldIsCorrect && $newIsCorrect) {
                    $delta = $question->question_points;
                }
                $currentScore = $quizUser->pivot->score;
                $newScore = $currentScore + $delta;
                $quiz->users()
                    ->wherePivot('attempt_number', $quizUser->pivot->attempt_number)
                    ->updateExistingPivot($user->id, ['score' => $newScore]);
            }
        } else {
            $userAnswer = new QuizUserAnswer();
            $userAnswer->quiz_user_id = $quizUser->pivot->id;
            $userAnswer->question_id = $question->id;
            $userAnswer->answer = $newAnswerValue;
            $userAnswer->is_correct = $newIsCorrect;
            $userAnswer->save();

            if ($newIsCorrect) {
                $currentScore = $quizUser->pivot->score;
                $newScore = $currentScore + $question->question_points;
                $quiz->users()
                    ->wherePivot('attempt_number', $quizUser->pivot->attempt_number)
                    ->updateExistingPivot($user->id, ['score' => $newScore]);
            }
        }

        if ($questionIndex < $quiz->questions->count() - 1) {
            return redirect()->route('quizzes.take', ['quiz' => $quiz->id, 'questionIndex' => $questionIndex + 1]);
        } else {
            $quiz->users()
                ->wherePivot('attempt_number', $quizUser->pivot->attempt_number)
                ->updateExistingPivot($user->id, [
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

            return redirect()->route('quizzes.results', [
                'quiz'           => $quiz->id,
                'attempt_number' => $quizUser->pivot->attempt_number
            ]);
        }
    }


    public function timeout(Quiz $quiz)
    {
        $user = Auth::user();
        $quizUser = $quiz->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'in_progress')
            ->first();

        if ($quizUser) {
            QuizUserAnswer::where('quiz_user_id', $quizUser->pivot->id)->delete();

            $quiz->users()
                ->wherePivot('attempt_number', $quizUser->pivot->attempt_number)
                ->updateExistingPivot($user->id, [
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
        }

        return redirect()->route('quizzes.results', [
            'quiz'           => $quiz->id,
            'attempt_number' => $quizUser->pivot->attempt_number
        ])->with('error', 'Время вышло. Ваши ответы не засчитаны!');
    }

    public function results(Quiz $quiz, $attemptNumber)
    {
        $this->authorize('view', $quiz);
        $user = Auth::user();
        $quizUser = $quiz->users()
            ->where('users.id', $user->id)
            ->wherePivot('attempt_number', $attemptNumber)
            ->first();

        if (!$quizUser) {
            return redirect()->route('quizzes.index')->with('error', 'Результаты не найдены.');
        }

        $score = $quizUser->pivot->score;

        return view('quizzes.results', compact('quiz', 'score', 'attemptNumber')); 
    }

    public function resultsDetails(Quiz $quiz, $attemptNumber)
    {
        // Проверяем авторизацию с использованием новой политики 'resultsDetails'
        $this->authorize('resultsDetails', $quiz); 

        $user = Auth::user();

        // Проверяем, является ли текущий пользователь создателем теста или администратором
        if ($quiz->user_id === $user->id || $user->isAdmin()) {
            // Если пользователь - создатель или админ, то $quizUser должен быть найден по ID теста и номеру попытки
            $quizUser = $quiz->users()
                ->wherePivot('attempt_number', $attemptNumber)
                ->first(); // Получаем первую попавшуюся попытку с таким номером для данного теста
        } else {
            // Если обычный пользователь, то ищем его конкретную попытку
            $quizUser = $quiz->users()
                ->where('users.id', $user->id)
                ->wherePivot('attempt_number', $attemptNumber)
                ->first();
        }

        if (!$quizUser) {
            return redirect()->route('quizzes.index')->with('error', 'Детали попытки не найдены.');
        }

        $answers = QuizUserAnswer::where('quiz_user_id', $quizUser->pivot->id)->get();

        $questions = $quiz->questions()->paginate(5);

        return view('quizzes.results_details', compact('quiz', 'answers', 'quizUser', 'questions'));
    }

    public function myQuizzes(Request $request)
    {
        $quizzesQuery = Quiz::where('user_id', Auth::id());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $quizzesQuery->where('title', 'like', '%' . $search . '%');
        }

        if ($request->filled('access')) {
            $quizzesQuery->where('access_type', $request->input('access'));
        }

        if ($request->filled('time_limit_type')) {
            $quizzesQuery->where('time_limit_type', $request->input('time_limit_type'));
        }

        if ($request->filled('attempt_limit_type')) {
            $quizzesQuery->where('attempt_limit_type', $request->input('attempt_limit_type'));
        }

        if ($request->filled('is_published')) {
            $quizzesQuery->where('is_published', $request->input('is_published'));
        }


        $allowedSorts = [
            'is_published',      
            'title',             
            'time_limit_type',  
            'time_limit',       
            'attempt_limit_type',
            'attempt_limit',     
        ];
        $sort = $request->input('sort', 'created_at'); 
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'desc');

        $quizzesQuery->orderBy($sort, $order);

        $quizzes = $quizzesQuery->paginate(20)->appends($request->query());

        return view('quizzes.my_quizzes', compact('quizzes'));
    }


    public function completedQuizzes(Request $request)
    {
        $user = Auth::user();
        $attemptsQuery = $user->quizzes()
            ->wherePivot('status', 'completed') 
            ->withPivot('score', 'attempt_number', 'started_at', 'completed_at'); 

        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $attemptsQuery->where('title', 'like', '%' . $search . '%');
        }

        if ($request->has('min_score') && $request->input('min_score')) {
            $attemptsQuery->wherePivot('score', '>=', $request->input('min_score'));
        }

        if ($request->has('max_score') && $request->input('max_score')) {
            $attemptsQuery->wherePivot('score', '<=', $request->input('max_score'));
        }

        if ($request->has('from_date') && $request->input('from_date')) {
            $fromDate = Carbon::parse($request->input('from_date'));
            $attemptsQuery->wherePivot('started_at', '>=', $fromDate);
        }

        if ($request->has('to_date') && $request->input('to_date')) {
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();
            $attemptsQuery->wherePivot('started_at', '<=', $toDate);
        }

        $sort = $request->input('sort', 'pivot_attempt_number'); 
        $order = $request->input('order', 'desc');

        if ($sort === 'time_spent') {
            $attemptsCollection = $attemptsQuery->get()->map(function ($attempt) {
                $attempt->time_spent = $this->calculateTimeSpent($attempt); 
                return $attempt;
            })->sortBy("time_spent", SORT_NUMERIC, $order === 'desc');

            $perPage = 20;
            $currentPage = LengthAwarePaginator::resolveCurrentPage() ?: 1;
            $items = $attemptsCollection->values();
            $currentPageItems = $items->forPage($currentPage, $perPage);

            $attempts = new LengthAwarePaginator(
                $currentPageItems,
                $items->count(),
                $perPage,
                $currentPage,
                ['path' => \Request::url(), 'query' => \Request::query()]
            );
        } else {
            $attemptsQuery->orderBy($sort, $order);
            $attempts = $attemptsQuery->paginate(20)->appends($request->query());
        }

        return view('quizzes.completed_quizzes', compact('attempts'));
    }

    private function calculateTimeSpent($attempt)
    {
        if ($attempt->pivot->started_at && $attempt->pivot->completed_at) {
            return Carbon::parse($attempt->pivot->completed_at)
                ->diffInMinutes(Carbon::parse($attempt->pivot->started_at));
        }
        return null;
    }

    public function publish(Quiz $quiz)
    {
        $this->authorize('publish', $quiz);
        $questionsCount = $quiz->questions()->count();
        if ($questionsCount === 0) {
            return back()->with('error', 'Невозможно опубликовать тест без вопросов.');
        }
        $quiz->update(['is_published' => true]);
        return redirect()->route('quizzes.index')->with('success', 'Тест успешно опубликован.');
    }

    public function deleteSelected(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'exists:quizzes,id,user_id,'.auth()->id()
        ]);

        Quiz::whereIn('id', $request->input('selected'))->delete();

        return redirect()->route('quizzes.myQuizzes')
                        ->with('success', 'Выбранные тесты успешно удалены.');
    }

    public function deleteAll(Request $request)
    {
        $user = auth()->user();
        $quizzesCount = $user->createdQuizzes()->count();

        if ($quizzesCount > 0) {
            $user->createdQuizzes()->delete();
        }

        return redirect()->route('quizzes.myQuizzes')
                        ->with('success', 'Все тесты успешно удалены.');
    }

    public function statistics(Quiz $quiz, Request $request)
    {
        $attemptsQuery = $quiz->users()
            ->wherePivot('status', 'completed') 
            ->withPivot('score', 'attempt_number', 'started_at', 'completed_at');

        if ($request->filled('search')) {
            $loginSearch = $request->input('search');
            $attemptsQuery->whereHas('author', function ($query) use ($loginSearch) {
                $query->where('login', 'like', '%' . $loginSearch . '%');
            });
        }
        if ($request->filled('min_score')) {
            $attemptsQuery->wherePivot('score', '>=', $request->input('min_score'));
        }

        if ($request->filled('max_score')) {
            $attemptsQuery->wherePivot('score', '<=', $request->input('max_score'));
        }
        if ($request->filled('from_date')) {
            $fromDate = Carbon::parse($request->input('from_date'));
            $attemptsQuery->wherePivot('started_at', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();
            $attemptsQuery->wherePivot('started_at', '<=', $toDate);
        }
        $validSortColumns = [
            'users.login' => 'users.login',
            'pivot_attempt_number' => 'attempt_number',
            'pivot_score' => 'score',
            'pivot_started_at' => 'started_at',
            'time_spent' => 'time_spent'
        ];
        $sort = $validSortColumns[$request->input('sort', 'pivot_attempt_number')] ?? 'pivot_attempt_number';
        $order = $request->input('order', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sort === 'time_spent') {
            $attempts = $attemptsQuery->get()->map(function ($attempt) {
                if ($attempt->pivot->started_at && $attempt->pivot->completed_at) {
                    $start = Carbon::parse($attempt->pivot->started_at);
                    $end = Carbon::parse($attempt->pivot->completed_at);
                    $attempt->time_spent = $end->diffInSeconds($start);
                } else {
                    $attempt->time_spent = PHP_INT_MAX; 
                }
                return $attempt;
            })->sortBy("time_spent", SORT_NUMERIC, $order === 'desc');

            $perPage = 20;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageItems = $attempts->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $attempts = new LengthAwarePaginator(
                $currentPageItems,
                $attempts->count(),
                $perPage,
                $currentPage,
                ['path' => \Request::url(), 'query' => \Request::query()]
            );
        } else {
            $attemptsQuery->orderBy($sort, $order);
            $attempts = $attemptsQuery->paginate(20);
        }
        return view('quizzes.statistics', compact('quiz', 'attempts', 'request'));
    }

}
