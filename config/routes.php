<?php

return [
    'user_get' => [
        'path' => '/users/{id}',
        'controller' => 'App\Controller\UserController::get',
        'methods' => ['GET'],
        'requirements' => ['id' => '\d+']
    ],
    // 'user_list' => [
    //     'path' => '/users',
    //     'controller' => 'App\Controller\UserController::list',
    //     'methods' => ['GET'],
    // ],
    // 'user_create' => [
    //     'path' => '/users',
    //     'controller' => 'App\Controller\UserController::create',
    //     'methods' => ['POST'],
    // ],
    // 'user_edit' => [
    //     'path' => '/users/{id}',
    //     'controller' => 'App\Controller\UserController::edit',
    //     'methods' => ['PUT', 'PATCH'],
    //     'requirements' => ['id' => '\d+']
    // ],
    // 'user_delete' => [
    //     'path' => '/users/{id}',
    //     'controller' => 'App\Controller\UserController::delete',
    //     'methods' => ['DELETE'],
    //     'requirements' => ['id' => '\d+']
    // ],
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
    'user_reset_password' => [
        'path' => '/users/{id}/reset-password',
        'controller' => 'App\Controller\UserController::resetPassword',
        'methods' => ['POST'],
        'requirements' => ['id' => '\d+']
    ],
    'user_register' => [
        'path' => '/guest/register',
        'controller' => 'App\Controller\UserController::register',
        'methods' => ['POST'],
    ],
    'user_activate' => [
        'path' => '/guest/activate',
        'controller' => 'App\Controller\UserController::activate',
        'methods' => ['POST'],
    ],
    'password_forgot' => [
        'path' => '/guest/forgot-password',
        'controller' => 'App\Controller\UserController::passwordForgot',
        'methods' => ['POST'],
    ],
];