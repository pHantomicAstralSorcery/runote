<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Option;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        return [
            'quiz_id' => Quiz::factory(), // Создание нового теста для каждого вопроса
            'question_image' => $this->faker->imageUrl(),
            'question_text' => $this->faker->sentence(),
            'question_description' => $this->faker->paragraph(),
            'question_type' => $this->faker->randomElement(['single', 'multiple', 'text']),
            'question_points' => $this->faker->numberBetween(1, 10),
        ];
    }

public function configure()
    {
        return $this->afterCreating(function (Question $question) {
            // Для типа 'text' только один правильный ответ
            if ($question->question_type === 'text') {
                Option::factory()->for($question)->create([
                    'is_correct' => true,
                    'option_text' => $this->faker->sentence(),
                ]);
            } elseif ($question->question_type === 'multiple') {
                Option::factory()->count(2)->for($question)->create([
                    'is_correct' => true,
                ]);
                Option::factory()->count(2)->for($question)->create([
                    'is_correct' => false,
                ]);
            } else {
                Option::factory()->count(1)->for($question)->create([
                    'is_correct' => true,
                ]);
                Option::factory()->count(3)->for($question)->create([
                    'is_correct' => false,
                ]);
            }
        });
    }
}
