<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class OptionFactory extends Factory
{
    protected $model = Option::class;

    public function definition()
    {
        return [
            'question_id' => \App\Models\Question::factory(), // это будет работать, если Question создается в другом месте
            'option_text' => $this->faker->sentence(),
            'is_correct' => $this->faker->boolean(50), // случайно 50% вероятности для правильного ответа
        ];
    }
}
