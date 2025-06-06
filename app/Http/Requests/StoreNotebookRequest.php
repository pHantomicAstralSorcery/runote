<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotebookRequest extends FormRequest
{
    // Авторизация: любой аутентифицированный пользователь
    public function authorize(): bool
    {
        return true;
    }

    // Правила валидации
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'access'      => 'required|in:open,closed',
            'description' => 'nullable|string',
            // другие поля, если есть
        ];
    }
}
