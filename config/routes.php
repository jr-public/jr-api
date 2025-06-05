<?php

return [
    'guest' => [
        'user_login' => [
            'path' => '/guest/login',
            'controller' => 'App\Controller\UserController::login',
            'methods' => ['POST'],
        ],
        'user_register' => [
            'path' => '/guest/register',
            'controller' => 'App\Controller\UserController::register',
            'methods' => ['POST'],
        ],
        'user_password_forgot' => [
            'path' => '/guest/password-forgot',
            'controller' => 'App\Controller\UserController::passwordForgot',
            'methods' => ['POST'],
        ],
        'user_password_reset' => [
            'path' => '/guest/password-reset',
            'controller' => 'App\Controller\UserController::passwordReset',
            'methods' => ['POST'],
        ],
        'user_activate' => [
            'path' => '/guest/activate',
            'controller' => 'App\Controller\UserController::activate',
            'methods' => ['POST'],
        ],
    ],
    'user' => [
        'user_get' => [
            'path' => '/users/{id}',
            'controller' => 'App\Controller\UserController::get',
            'methods' => ['GET'],
            'requirements' => ['id' => '\d+']
        ],
        'user_block' => [
            'path' => '/users/{id}/block',
            'controller' => 'App\Controller\UserController::block',
            'methods' => ['POST'],
            'requirements' => ['id' => '\d+']
        ],
        'user_unblock' => [
            'path' => '/users/{id}/unblock',
            'controller' => 'App\Controller\UserController::unblock',
            'methods' => ['POST'],
            'requirements' => ['id' => '\d+']
        ],
    ]
];