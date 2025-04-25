<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Если у вас есть модели и политики, подключите их здесь:
use App\Models\Quiz;
use App\Policies\QuizPolicy;
use App\Models\Question;
use App\Policies\QuestionPolicy;
use App\Models\Workbook;
use App\Policies\WorkbookPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Массив соответствия моделей и политик.
     *
     * @var array
     */
    protected $policies = [
    Quiz::class => QuizPolicy::class,
    Question::class => QuestionPolicy::class,
    User::class => UserPolicy::class,
Workbook::class => WorkbookPolicy::class,
    ];

    /**
     * Регистрация политик.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

    }
}
