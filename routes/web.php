<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MentorNoteController;
use App\Http\Controllers\NoteReplyController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    WorkbookController, FieldController,
    NamedLinkController, ResponseController
};

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
Route::view('/auth', 'users.auth')->name('auth');
Route::post('/register', [UserController::class, 'register_post'])->name('register_post');
Route::post('/auth', [UserController::class, 'auth_post'])->name('auth_post');
});
     /*
     * ===============================
     *  Рабочая тетрадь (именные ссылки)
     * ===============================
     */
Route::get('links/{slug}', [NamedLinkController::class,'show'])
     ->middleware('check.access')
     ->name('links.show');
Route::post('links/{slug}', [ResponseController::class,'store'])
     ->middleware('check.access')
     ->name('links.submit');

Route::middleware(['auth', 'unsetadminmode'])->group(function () {
     /*
     * ===============================
     *  Выход
     * ===============================
     */
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');

     /*
     * ===============================
     *  Рабочая тетрадь (создатель)
     * ===============================
     */
     Route::resource('workbooks', WorkbookController::class);
    Route::post('workbooks/{workbook}/revert/{version}', [WorkbookController::class,'revert'])
         ->name('workbooks.revert');
    Route::resource('workbooks.fields', FieldController::class);
    Route::resource('workbooks.links', NamedLinkController::class)
         ->only(['index','store','destroy']);
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
