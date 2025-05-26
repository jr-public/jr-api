<?php

return [
    'user_get' => [
        'path' => '/users/{id}',
        'controller' => 'App\Controller\UserController::get',
        'methods' => ['GET'],
    ],
    'user_list' => [
        'path' => '/users',
        'controller' => 'App\Controller\UserController::list',
        'methods' => ['GET'],
    ],
    'user_create' => [
        'path' => '/users',
        'controller' => 'App\Controller\UserController::create',
        'methods' => ['POST'],
    ],
];
