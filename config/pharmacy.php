<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Локальная LLM (Ollama)
    |--------------------------------------------------------------------------
    */
    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434/api/chat'),
        'model' => env('OLLAMA_MODEL', 'qwen3:8b'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 120),

        // Сколько товаров максимум уходит в системный промпт.
        // В старой версии туда лился ВЕСЬ каталог — на паре тысяч позиций
        // это гарантированный вылет по контексту.
        'catalog_limit' => (int) env('OLLAMA_CATALOG_LIMIT', 60),

        'rate_limit' => [
            'per_minute' => 12,
            'per_hour' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ЮKassa
    |--------------------------------------------------------------------------
    */
    'yookassa' => [
        'shop_id' => env('YOOKASSA_SHOP_ID'),
        'secret_key' => env('YOOKASSA_SECRET_KEY'),
        'api_url' => env('YOOKASSA_API_URL', 'https://api.yookassa.ru/v3'),

        // Хосты, на которые разрешено редиректить пользователя для оплаты.
        'allowed_pay_hosts' => ['yoomoney.ru', 'www.yoomoney.ru'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Демо-симуляция доставки
    |--------------------------------------------------------------------------
    | Заказ сам продвигается по статусам раз в N секунд. В проде это надо
    | выключить (ORDER_SIMULATION_ENABLED=false) и завести реальную логику.
    */
    'order_simulation' => [
        'enabled' => (bool) env('ORDER_SIMULATION_ENABLED', true),
        'step_seconds' => (int) env('ORDER_STATUS_STEP_SECONDS', 60),
        'chain' => ['new', 'processing', 'shipped', 'at_hub', 'sent_to_pickup', 'ready_for_pickup'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Подтверждение почты кодом
    |--------------------------------------------------------------------------
    */
    'email_verification' => [
        'code_ttl_minutes' => 10,
        'max_attempts' => 10,
        'resend_cooldown_seconds' => 60,
        'max_per_hour' => 5,
    ],
];
