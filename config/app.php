<?php

$fake_app_env = 'development';
$url = 'http://localhost';


return [
    'name' 		=> getenv('APP_PHP_NAME'),
    'version' 	=> '0.0.0',
    'env'		=> $fake_app_env,
    'debug' 	=> $fake_app_env == 'development',
    // 'env'		=> getenv('APP_ENV'),
    // 'debug' 	=> getenv('APP_ENV') == 'development'
    'url' => $url,
    'confirm_path' => $url . '/confirm.php',
    // 'timezone' => 'UTC',
    // 'locale' => 'en',
];
