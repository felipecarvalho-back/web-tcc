<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Secret Key
    |--------------------------------------------------------------------------
    |
    | Chave secreta usada para assinar e validar os tokens JWT.
    | Caso não definida, utiliza o APP_KEY da aplicação como fallback.
    |
    */
    'secret' => env('JWT_SECRET', env('APP_KEY')),

    /*
    |--------------------------------------------------------------------------
    | JWT Time To Live (TTL)
    |--------------------------------------------------------------------------
    |
    | Tempo de expiração do token em minutos. Padrão: 60 minutos (1 hora).
    |
    */
    'ttl' => (int) env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | JWT Algorithm
    |--------------------------------------------------------------------------
    |
    | Algoritmo de assinatura do token JWT.
    |
    */
    'algo' => env('JWT_ALGO', 'HS256'),
];
