<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'question_text' => 'required|string|max:255',
            'question_description' => 'nullable|string',
            'question_points' => 'required|integer|min:1',
            'question_type' => 'required|in:single,multiple,text',
            'options' => 'required|array',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.is_correct' => 'required|boolean', // Проверка на boolean
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Adjust max size as needed
        ];
    }

    protected function prepareForValidation()
    {
        $data = $this->all();

        // Преобразуем is_correct из строки в boolean
        if (isset($data['options'])) {
            foreach ($data['options'] as &$option) {
                if (isset($option['is_correct'])) {
                    $option['is_correct'] = filter_var($option['is_correct'], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        $this->replace($data);
    }

    public function messages()
    {
        return [
            'question_text.required' => 'Поле вопроса обязательно для заполнения.',
            'question_text.max' => 'Поле вопроса не должно превышать 255 символов.',
            'question_description.string' => 'Описание вопроса должно быть строкой.',
            'question_points.required' => 'Поле баллов обязательно для заполнения.',
            'question_points.integer' => 'Баллы должны быть числом.',
            'question_points.min' => 'Минимальное значение баллов - 1.',
            'question_type.required' => 'Тип вопроса обязателен для выбора.',
            'question_type.in' => 'Недопустимый тип вопроса.',
            'options.required' => 'Необходимо указать варианты ответов.',
            'options.array' => 'Варианты ответов должны быть массивом.',
            'options.*.option_text.required' => 'Текст варианта ответа обязателен для заполнения.',
            'options.*.option_text.max' => 'Текст варианта ответа не должен превышать 255 символов.',
            'options.*.is_correct.required' => 'Поле правильности ответа обязательно для заполнения.',
            'options.*.is_correct.boolean' => 'Поле правильности ответа должно быть логическим значением (true/false).',
            'question_image.image' => 'Загруженный файл должен быть изображением.',
            'question_image.mimes' => 'Изображение должно быть одного из следующих форматов: jpeg, png, jpg, gif, svg.',
            'question_image.max' => 'Размер изображения не должен превышать 2MB.',
        ];
    }
}