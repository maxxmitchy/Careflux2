<?php

return [
    'telegram_channels' => [
        'admin' => env('TELEGRAM_ADMIN_CHAT_ID'),
        'payments' => env('TELEGRAM_PAYMENT_CHAT_ID'),
        'assistant' => env('TELEGRAM_ASSISTANT_CHAT_ID'),
        // Add more channels here as needed
    ],
    'default_support_phone' => env('CAREFLUX_SUPPORT_PHONE', '2348147578314'),
];
