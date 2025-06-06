<?php

namespace App\Observers;

use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;

class AuditableObserver
{
    public function saved($model)
    {
        // Получаем старые и новые атрибуты модели
        $old = $model->getOriginal();
        $new = $model->getAttributes();

        // Функция для безопасного строки: массивы — в JSON, остальное приводим к строке
        $stringify = function ($value) {
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return (string) $value;
        };

        // Приводим все значения к строкам, чтобы избежать ошибки
        $oldString = array_map($stringify, $old);
        $newString = array_map($stringify, $new);

        // Сравниваем
        $changes = array_diff_assoc($newString, $oldString);

        if (count($changes) > 0) {
            Audit::create([
                'user_type'     => get_class(Auth::user()),
                'user_id'       => Auth::id(),
                'event'         => 'updated',
                'auditable_type'=> get_class($model),
                'auditable_id'  => $model->getKey(),
                'old_values'    => json_encode(array_intersect_key($old, $changes)),
                'new_values'    => json_encode(array_intersect_key($new, $changes)),
                'url'           => request()->fullUrl(),
                'ip_address'    => request()->ip(),
                'user_agent'    => substr(request()->userAgent() ?? '', 0, 1023),
                'tags'          => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
