<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'unique:users,login',
                'regex:/^[a-zA-Z0-9_-]+$/',
            ],
            'email' => [
                'required',
                'unique:users,email',
                'email',
            ],
            'password' => [
                'required',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
            'password_repeat' => [
                'required',
                'same:password'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Это поле обязательно для заполнения.',
            'login.unique' => 'Логин уже занят.',
            'login.regex' => 'Логин должен содержать только латинские буквы, цифры, нижнее подчеркивание и тире.',
            'email.unique' => 'Email уже занят.',
            'email.email' => 'Неверный формат email.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.regex' => 'Пароль должен содержать минимум одну заглавную букву, одну цифру и один специальный символ.',
            'password_repeat.same' => 'Пароли не совпадают.',
        ];
    }
}
