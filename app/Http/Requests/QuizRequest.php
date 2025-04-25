<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Здесь можно добавить проверку прав доступа, если необходимо
    }

public function rules()
{
    $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'access_type' => 'required|in:open,link,hidden',
        'time_limit_type' => 'required|in:none,custom',
        'attempt_limit_type' => 'required|in:none,custom',
    ];

    if ($this->input('time_limit_type') === 'custom') {
        $rules['time_limit'] = 'required|integer|min:1';  // обязательное поле только если тип "custom"
    } else {
        $rules['time_limit'] = 'nullable';  // если тип "none", то поле не обязательно
    }

    if ($this->input('attempt_limit_type') === 'custom') {
        $rules['attempt_limit'] = 'required|integer|min:1';  // обязательное поле только если тип "custom"
    } else {
        $rules['attempt_limit'] = 'nullable';  // если тип "none", то поле не обязательно
    }

    return $rules;
}


    public function messages()
    {
        return [
            'title.required' => 'Поле "Название" обязательно для заполнения.',
            'title.string' => 'Поле "Название" должно быть строкой.',
            'title.max' => 'Поле "Название" не может содержать более 255 символов.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'access_type.required' => 'Поле "Тип доступа" обязательно для заполнения.',
            'access_type.in' => 'Поле "Тип доступа" содержит недопустимое значение.',
            'time_limit_type.required' => 'Поле "Тип ограничения по времени" обязательно для заполнения.',
            'time_limit_type.in' => 'Поле "Тип ограничения по времени" содержит недопустимое значение.',
            'time_limit.integer' => 'Поле "Ограничение по времени" должно быть числом.',
            'time_limit.min' => 'Поле "Ограничение по времени" должно быть больше или равно 1.',
            'time_limit.required' => 'Поле "Ограничение по времени" обязательно для заполнения.',
            'attempt_limit_type.required' => 'Поле "Тип ограничения по времени" обязательно для заполнения.',
            'attempt_limit_type.in' => 'Поле "Тип ограничения по времени" содержит недопустимое значение.',
            'attempt_limit.integer' => 'Поле "Ограничение по времени" должно быть числом.',
            'attempt_limit.min' => 'Поле "Ограничение по времени" должно быть больше или равно 1.',
            'attempt_limit.required' => 'Поле "Ограничение по времени" обязательно для заполнения.',
        ];
    }
}