<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\NotebookController;
use App\Http\Controllers\NamedLinkController;
use App\Http\Controllers\StudentNotebookInstanceController;
use Illuminate\Support\Facades\Route;

/*
 * ===============================
 * Гостевые страницы
 * ===============================
 */
Route::middleware('unsetadminmode')->group(function () {
    Route::get('/', function () {
        return view('main');
    })->name('/');
    Route::get('/about_us', function () {
        return view('about_us');
    })->name('about_us');
    Route::view('/blocked', 'blocked')->name('blocked');

    /*
     * ===============================
     * Авторизация/Регистрация
     * ===============================
     */
    Route::view('/register', 'users.register')->name('register');
    Route::view('/requirement', 'users.requirement')->name('requirement');
    Route::view('/auth', 'users.auth')->name('auth');
    Route::post('/register', [UserController::class, 'register_post'])->name('register_post');
    Route::post('/auth', [UserController::class, 'auth_post'])->name('auth_post');

    /*
     * ===============================
     * Тетради (ссылка для учеников) - Public access routes for student links
     * ===============================
     */
    Route::get('named-links/{token}', [NamedLinkController::class, 'view'])->name('named_links.view');
    Route::post('named-links/{token}/submit', [NamedLinkController::class, 'submit'])->name('named_links.submit');
    Route::post('named-links/{token}/delete-response/{fieldUuid}', [NamedLinkController::class, 'deleteResponse'])->name('named_links.deleteResponse');
});

/*
 * ===============================
 * Авторизованные пользователи - Authenticated user routes
 * ===============================
 */
Route::middleware(['auth', 'unsetadminmode'])->group(function () {
    /*
     * ===============================
     * Выход - Logout
     * ===============================
     */
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');

    /*
     * ===============================
     * Тесты - Quizzes
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
     * Тетради - Notebooks (Teacher's side)
     * ===============================
     */
    Route::resource('notebooks', NotebookController::class);

    // Grouping notebook-specific routes under a prefix for clarity
    Route::prefix('notebooks/{notebook}')->group(function () {
        // Маршрут для сохранения снимка содержимого тетради
        Route::post('/save-snapshot', [NotebookController::class, 'saveSnapshot'])
            ->name('notebooks.saveSnapshot');

        // Маршрут для обновления общих настроек тетради (AJAX)
        Route::put('/general-settings', [NotebookController::class, 'update'])
            ->name('notebooks.updateGeneralSettings');

        // Маршруты для управления снимками тетради
        Route::get('/snapshots', [NotebookController::class, 'getSnapshots'])
            ->name('notebooks.snapshots.index');
        Route::get('/snapshots/{notebookSnapshot}/content', [NotebookController::class, 'getSnapshotContent'])
            ->name('notebooks.snapshots.content');
        Route::post('/revert-snapshot/{notebookSnapshot}', [NotebookController::class, 'revertSnapshot'])
            ->name('notebooks.revertSnapshot');

        // Новый маршрут для получения статуса доступа к тетради
        Route::get('/access-status', [NotebookController::class, 'accessStatus'])->name('notebooks.access-status');

        // Именные ссылки (вложенные маршруты для конкретной тетради)
        Route::get('/named-links', [NamedLinkController::class, 'index'])
            ->name('named_links.index');
        Route::post('/named-links', [NamedLinkController::class, 'store'])
            ->name('named_links.store');
        Route::put('/named-links/{named_link}', [NamedLinkController::class, 'update'])
            ->name('named_links.update');
        Route::delete('/named-links/{named_link}', [NamedLinkController::class, 'destroy'])
            ->name('named_links.destroy');

        // Маршруты для массовых операций с named links
        // Это маршрут, который вызывает проблему. ИСПРАВЛЕНИЕ: Routee -> Route
        Route::post('named-links/destroy-all', [NamedLinkController::class, 'destroyAll'])->name('named_links.destroyAll');
        Route::post('named-links/toggle-all', [NamedLinkController::class, 'toggleAll'])->name('named_links.toggleAll');

        // Маршрут для новой страницы общей статистики по ссылкам
        Route::get('overall-statistics', [NotebookController::class, 'showOverallStatistics'])->name('notebooks.overall_statistics');
    });

    /*
     * ===============================
     * Прогресс экземпляров тетрадей учеников - Student Notebook Instance Progress
     * ===============================
     */
    Route::get('/student-notebook-instances/{studentNotebookInstance}/progress', [StudentNotebookInstanceController::class, 'showProgress'])
        ->name('student_notebook_instances.progress');

    // === НОВЫЙ МАРШРУТ ===
    // Маршрут для маркировки корректности ответа ученика (только для преподавателя)
    // Используем StudentNotebookInstanceController, так как это связано с ответами учеников
    Route::post('/student-responses/{studentResponse}/mark-correctness', [StudentNotebookInstanceController::class, 'markResponseCorrectness'])
        ->name('student_responses.mark_correctness');


    /*
     * ===============================
     * Блоки и страницы (для редактора) - Blocks and Pages (for editor)
     * ===============================
     */
    Route::post('pages/{page}/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::put('blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::delete('blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
    Route::post('pages/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');
    Route::post('blocks/upload-image', [BlockController::class, 'uploadImage'])
         ->name('blocks.uploadImage');
});

/*
 * ===============================
 * Админ панель - Admin Panel
 * ===============================
 */
Route::middleware(['auth', 'admin'])->group(function () {
    
    // Этот маршрут у вас уже есть и остается без изменений
    Route::post('/toggle-admin-mode', [AdminController::class, 'toggleMode'])
        ->name('admin.toggle-mode');

    // --- ОБНОВЛЕННЫЕ МАРШРУТЫ ДЛЯ АДМИН-ПАНЕЛИ ---
    // Группируем все маршруты админки для удобства
    Route::prefix('admin-panel')->name('admin.')->group(function () {
        
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        // Сюда можно будет добавить маршруты для edit, update, destroy пользователей
        
        Route::get('/quizzes', [AdminController::class, 'quizzes'])->name('quizzes.index');
        // Маршруты для управления тестами
        
        Route::get('/notebooks', [AdminController::class, 'notebooks'])->name('notebooks.index');
        // Маршруты для управления тетрадями
    });

    // Перенаправление со старого URL на новый для совместимости
    // Removed the redirect to admin.dashboard
    Route::view('/admin-panel', 'admin_panel.index')->name('admin_panel.index');

});
