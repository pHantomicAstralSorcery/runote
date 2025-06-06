<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MentorNoteController;
use App\Http\Controllers\NoteReplyController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\NotebookController;
use App\Http\Controllers\NamedLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('unsetadminmode')->group(function () {
     /*
     * ===============================
     *  Гостевые страницы
     * ===============================
     */
Route::get('/', function () {
    return view('main');
})->name('/');
Route::get('/about_us', function () {
    return view('about_us');
})->name('about_us');
     /*
     * ===============================
     *  Авторизация/Регистрация
     * ===============================
     */
Route::view('/register', 'users.register')->name('register');
Route::view('/requirement', 'users.requirement')->name('requirement');
Route::view('/auth', 'users.auth')->name('auth');
Route::post('/register', [UserController::class, 'register_post'])->name('register_post');
Route::post('/auth', [UserController::class, 'auth_post'])->name('auth_post');
     /*
     * ===============================
     *  Тетради (ссылка)
     * ===============================
     */
Route::get('named-links/{token}', [NamedLinkController::class, 'view'])->name('named_links.view');
Route::post('named-links/{token}/submit', [NamedLinkController::class, 'submit'])->name('named_links.submit');
});

Route::middleware(['auth', 'unsetadminmode'])->group(function () {
     /*
     * ===============================
     *  Выход
     * ===============================
     */
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');
     /*
     * ===============================
     *  Тесты
     * ===============================
     */
    Route::resource('quizzes', QuizController::class);

    Route::prefix('quizzes/{quiz}')->group(function () {
        Route::resource('questions', QuestionController::class)->except(['show']);
    });

    Route::post('/quizzes/{quiz}/publish', [QuizController::class, 'publish'])->name('quizzes.publish');
    Route::get('/quizzes/{quiz}/publish', function ($quiz) {
        abort(403);
    })->name('quizzes.publish.get');

    Route::get('quizzes/{quiz}/take/{questionIndex?}', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('quizzes/{quiz}/question/{questionIndex}/submit', [QuizController::class, 'submitAnswer'])->name('quizzes.submitAnswer');
    Route::get('/quizzes/{quiz}/results/{attempt_number}', [QuizController::class, 'results'])->name('quizzes.results');
    Route::get('/quizzes/{quiz}/results/details/{attempt_number}', [QuizController::class, 'resultsDetails'])->name('quizzes.results.details');
    Route::get('/quizzes/{quiz}/statistics', [QuizController::class, 'statistics'])->name('quizzes.statistics');
    Route::get('/completed-quizzes', [QuizController::class, 'completedQuizzes'])->name('quizzes.completedQuizzes');
    Route::view('/quizzes/{quiz}/no-questions', 'quizzes.no_questions')->name('quizzes.no_questions');
    Route::get('/my-quizzes', [QuizController::class, 'myQuizzes'])->name('quizzes.myQuizzes');
    Route::post('/my-quizzes/delete-selected', [QuizController::class, 'deleteSelected'])->name('quizzes.deleteSelected');
    Route::post('/my-quizzes/delete-all', [QuizController::class, 'deleteAll'])->name('quizzes.deleteAll');
    Route::post('/quizzes/{quiz}/questions/delete-selected', [QuestionController::class, 'deleteSelected'])->name('questions.deleteSelected');
    Route::post('/quizzes/{quiz}/questions/delete-all', [QuestionController::class, 'deleteAll'])->name('questions.deleteAll');
    Route::get('/quizzes/{quiz}/timeout', [QuizController::class, 'timeout'])->name('quizzes.timeout');
     /*
     * ===============================
     *  Тетради
     * ===============================
     */
    Route::resource('notebooks', NotebookController::class);

    // Именные ссылки (вложенные маршруты для конкретной тетради)
    Route::get('notebooks/{notebook}/named-links', [NamedLinkController::class, 'index'])
        ->name('named_links.index');
    Route::get('notebooks/{notebook}/named-links/create', [NamedLinkController::class, 'create'])
        ->name('named_links.create');
    Route::post('notebooks/{notebook}/named-links', [NamedLinkController::class, 'store'])
        ->name('named_links.store');
    Route::get('notebooks/{notebook}/named-links/{namedLink}/edit', [NamedLinkController::class, 'edit'])
        ->name('named_links.edit');
    Route::put('notebooks/{notebook}/named-links/{namedLink}', [NamedLinkController::class, 'update'])
        ->name('named_links.update');
    Route::delete('notebooks/{notebook}/named-links/{namedLink}', [NamedLinkController::class, 'destroy'])
        ->name('named_links.destroy');
Route::post('notebooks/{notebook}/save', [NotebookController::class, 'saveContent'])
    ->name('notebooks.save');

    Route::post('pages/{page}/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::put('blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::delete('blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
    Route::post('pages/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');
    Route::post('blocks/upload-image', [BlockController::class, 'uploadImage'])
         ->name('blocks.uploadImage');
});

Route::middleware(['auth', 'admin'])->group(function () {
     /*
     * ===============================
     *  Админ панель
     * ===============================
     */
    Route::post('/toggle-admin-mode', [AdminController::class, 'toggleMode'])
        ->name('admin.toggle-mode');
    
    Route::get('/admin-panel', function () {
        session(['admin_mode' => true]);
        return view('admin_panel.index');
    })->name('admin_panel.index');
});
