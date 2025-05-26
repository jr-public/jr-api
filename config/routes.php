<?php

return [
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
    'user_reset_password' => [
        'path' => '/users/{id}/reset-password',
        'controller' => 'App\Controller\UserController::resetPassword',
        'methods' => ['POST'],
        'requirements' => ['id' => '\d+']
    ],
];