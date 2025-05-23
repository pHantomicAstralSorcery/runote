<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Включение аудита
    |--------------------------------------------------------------------------
    |
    | Управляет глобальным включением/отключением аудита.
    |
    */
    'enabled' => env('AUDITING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Модель для хранения аудита
    |--------------------------------------------------------------------------
    */
    'implementation' => OwenIt\Auditing\Models\Audit::class,

    /*
    |--------------------------------------------------------------------------
    | Настройки пользователя (кто совершил действие)
    |--------------------------------------------------------------------------
    */
    'user' => [
        // префикс для morph-подключения (в таблице audits будет user_type/user_id)
        'morph_prefix' => 'user',
        // какие guard’ы проверяем для текущего аутентифицированного юзера
        'guards' => [
            'web',
            'api',
        ],
        // резолвер, определяющий user_id
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Дополнительные резолверы: IP, User-Agent, URL
    |--------------------------------------------------------------------------
    */
    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url'        => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | События Eloquent, которые мы аудируем
    |--------------------------------------------------------------------------
    |
    | created   — создание записи
    | updated   — обновление
    | deleted   — удаление (soft или hard)
    | restored  — восстановление soft‑удалённых
    |
    */
    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    /*
    |--------------------------------------------------------------------------
    | Режим строгой фильтрации полей
    |--------------------------------------------------------------------------
    |
    | false — аудируем все атрибуты модели (кроме явно исключённых)
    | true  — аудируем только изменившиеся и указанные в $visible
    |
    */
    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Глобальный список полей, которые никогда не должны аудироваться
    |--------------------------------------------------------------------------
    */
    'exclude' => [
        'remember_token',
        // можно добавить здесь другие служебные поля
    ],

    /*
    |--------------------------------------------------------------------------
    | Должны ли сохраняться записи, если old_values и new_values пусты?
    |--------------------------------------------------------------------------
    */
    'empty_values' => false,
    'allowed_empty_values' => [
        'retrieved',
    ],

    /*
    |--------------------------------------------------------------------------
    | Можно ли аудировать массивы целиком (JSON-поля)
    |--------------------------------------------------------------------------
    */
    'allowed_array_values' => true,

    /*
    |--------------------------------------------------------------------------
    | Сохранять ли стандартные временные метки моделей
    |--------------------------------------------------------------------------
    | created_at, updated_at, deleted_at — попадут в Audit::old_values/new_values
    */
    'timestamps' => true,

    /*
    |--------------------------------------------------------------------------
    | Ограничение на число записей аудита у одной модели
    |--------------------------------------------------------------------------
    | 0 = без ограничений
    */
    'threshold' => 0,

    /*
    |--------------------------------------------------------------------------
    | Драйвер аудита
    |--------------------------------------------------------------------------
    */
    'driver' => 'database',

    'drivers' => [
        'database' => [
            'table'      => 'audits',
            'connection' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Запись аудита в очередь
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enable'     => false,
        'connection' => 'sync',
        'queue'      => 'default',
        'delay'      => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Аудит консольных команд (artisan)
    |--------------------------------------------------------------------------
    */
    'console' => false,
];
