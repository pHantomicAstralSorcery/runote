<?php
namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

public function definition()
    {
        $timeLimitType = $this->faker->randomElement(['none', 'custom']);
        $attemptLimitType = $this->faker->randomElement(['none', 'custom']);

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'access_type' => $this->faker->randomElement(['open', 'link', 'hidden']),
            'time_limit_type' => $timeLimitType,
            'attempt_limit_type' => $attemptLimitType,
            'time_limit' => $timeLimitType === 'custom' ? $this->faker->numberBetween(1, 60) : null,
            'attempt_limit' => $attemptLimitType === 'custom' ? $this->faker->numberBetween(1, 10) : null,
            'user_id' => User::inRandomOrder()->first()->id, // Получаем случайного пользователя
            'is_published' => $this->faker->boolean(30), // 30% шанс быть опубликованным
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Quiz $quiz) {
            // Создаем 10 вопросов для каждого теста
            Question::factory()
                ->count(10)
                ->for($quiz)
                ->create();  // Создаем вопросы и автоматически добавляются опции
        });
    }
}
